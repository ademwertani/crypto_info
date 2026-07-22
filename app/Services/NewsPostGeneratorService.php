<?php

namespace App\Services;

use App\Exceptions\NewsPostGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Drafts a NewsPost from a real RSS item (see NewsApiService). Unlike
 * MoneyPageGeneratorService — which writes about evergreen, generic topics —
 * this deals with real, dated events, so the prompt is deliberately strict:
 * expand ONLY the facts given, never invent additional details, names,
 * figures or quotes. The original RSS title is never rewritten by the AI
 * (see GenerateNewsPosts) — only the body/excerpt/meta fields are generated.
 */
class NewsPostGeneratorService
{
    private const MAX_TOKENS = 1024;

    private const TIMEOUT_SECONDS = 60;

    private const MAX_RETRIES = 3;

    /**
     * @param array{title: string, summary: ?string, url: string, source: ?string} $item
     * @return array<string, mixed> Ready to spread into NewsPost::create().
     *
     * @throws NewsPostGenerationException
     */
    public function generate(array $item): array
    {
        $response = $this->callGroq(
            $this->buildSystemPrompt(),
            $this->buildUserPrompt($item),
        );

        $data = $this->parseResponse($response, $item['title']);

        return [
            'excerpt' => Str::limit((string) $data['excerpt'], 300, ''),
            'content' => (string) $data['content_html'],
            'meta_title' => Str::limit((string) $data['meta_title'], 60, ''),
            'meta_description' => (string) $data['meta_description'],
        ];
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a crypto news editor for CryptoInfo, a cryptocurrency market data website.

You will be given a title, a short summary and a source name for a real news
item that is already public. Your job is to expand it into a short, neutral
news post — you must NEVER invent facts, figures, quotes, names or events
beyond what is explicitly given below. If the provided summary is too thin to
expand meaningfully, write a SHORT post rather than inventing additional
detail to fill space.

Rules:
- Use ONLY the facts given in the user message. Do not speculate about
  causes, effects, or future outcomes beyond what is stated.
- Do not invent price figures, statistics, or named individuals not present
  in the source material.
- Neutral, factual, journalistic tone — no hype, no financial advice, no
  price predictions.
- 150 to 350 words.
- Mention once, naturally, that this is based on reporting from the given
  source — do not cite any other source.

Output format — respond with ONLY a single valid JSON object. No markdown
code fences, no commentary before or after it. Exact shape:
{
  "meta_title": "string, under 60 characters",
  "meta_description": "string, 140-155 characters",
  "excerpt": "string, under 300 characters — a 1-2 sentence teaser",
  "content_html": "the full post body as HTML using <p> tags only"
}
PROMPT;
    }

    private function buildUserPrompt(array $item): string
    {
        $summary = $item['summary'] ?: 'No further summary provided by the source.';
        $source = $item['source'] ?: 'an external news source';

        return "Title: {$item['title']}\n".
            "Source: {$source}\n".
            "Source summary: {$summary}\n".
            "Source URL: {$item['url']}\n\n".
            'Respond with only the JSON object described in the system prompt.';
    }

    /**
     * @return array<string, mixed> Decoded Groq (OpenAI-compatible) chat completions response.
     *
     * @throws NewsPostGenerationException
     */
    private function callGroq(string $system, string $user): array
    {
        try {
            $response = Http::withToken((string) config('services.groq.api_key'))
                ->timeout(self::TIMEOUT_SECONDS)
                ->retry(
                    self::MAX_RETRIES,
                    fn (int $attempt) => min(1000 * (2 ** ($attempt - 1)), 8000),
                    function (\Throwable $exception) {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        if ($exception instanceof RequestException) {
                            $status = $exception->response->status();

                            return $status === 429 || $status >= 500;
                        }

                        return false;
                    }
                )
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'max_tokens' => self::MAX_TOKENS,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $e) {
            Log::error('NewsPostGeneratorService: Groq API call failed', ['message' => $e->getMessage()]);

            throw new NewsPostGenerationException('Groq API call failed: '.$e->getMessage(), previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     *
     * @throws NewsPostGenerationException
     */
    private function parseResponse(array $response, string $title): array
    {
        if (($response['choices'][0]['finish_reason'] ?? null) === 'length') {
            throw new NewsPostGenerationException("Groq response was truncated (max_tokens) for \"{$title}\".");
        }

        $text = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new NewsPostGenerationException("Groq response had no text content for \"{$title}\".");
        }

        $text = $this->stripCodeFences($text);
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new NewsPostGenerationException("Groq response was not valid JSON for \"{$title}\": ".json_last_error_msg());
        }

        foreach (['meta_title', 'meta_description', 'excerpt', 'content_html'] as $key) {
            if (empty($data[$key])) {
                throw new NewsPostGenerationException("Groq response missing required field \"{$key}\" for \"{$title}\".");
            }
        }

        return $data;
    }

    private function stripCodeFences(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', (string) $text);
        }

        return trim((string) $text);
    }
}
