<?php

namespace App\Services;

use App\Exceptions\ArticleGenerationException;
use App\Models\ArticleCategory;
use App\Models\Cryptocurrency;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Drafts a Blog Article for an evergreen, generic educational topic — same
 * spirit as MoneyPageGeneratorService, but outputs the `sections` array
 * shape Article/blog.show expect instead of intro_html/body_html/faq/cta.
 */
class ArticleGeneratorService
{
    private const MAX_TOKENS = 4096;

    private const TIMEOUT_SECONDS = 60;

    private const MAX_RETRIES = 3;

    /**
     * @param array{title: string, category?: string, related_coin_slugs?: array<int, string>} $spec
     * @return array<string, mixed> Ready to spread into Article::create().
     *
     * @throws ArticleGenerationException
     */
    public function generate(array $spec): array
    {
        $response = $this->callGroq(
            $this->buildSystemPrompt(),
            $this->buildUserPrompt($spec),
        );

        $data = $this->parseResponse($response, $spec['title']);

        return [
            'title' => $spec['title'],
            'article_category_id' => $this->resolveCategoryId($spec['category'] ?? null),
            'meta_title' => Str::limit((string) $data['meta_title'], 60, ''),
            // Column is varchar(191) — AppServiceProvider sets
            // Builder::defaultStringLength(191), not Laravel's usual 255
            // default. The prompt asks for 140-155 chars but that's not
            // enforced by the model, so hard-cap it here too.
            'meta_description' => Str::limit((string) $data['meta_description'], 191, ''),
            'excerpt' => Str::limit((string) $data['excerpt'], 300, ''),
            'sections' => array_values((array) $data['sections']),
            'related_coin_slugs' => $this->resolveCoinSlugs($spec['related_coin_slugs'] ?? []),
            'author_name' => 'CryptoInfo AI Draft',
        ];
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a professional crypto content writer for CryptoInfo, a cryptocurrency
market data website's educational blog.

Write 100% original, factual, genuinely useful content. Never copy or
closely paraphrase any existing source. This is an evergreen educational
article, not news — do not reference specific dates, prices, or recent
events.

Structure:
- Break the article into 3 to 6 logical sections, each starting with an
  <h2> heading.
- Total length: 600 to 1000 words across all sections combined.
- Include at least one <ul> or <ol> list somewhere if it fits naturally.

ABSOLUTE PROHIBITIONS — content violating any of these is unacceptable:
- No promises or guarantees of profit or gains.
- No affirmative price predictions ("X will reach $Y", "X is going to moon").
- No personalized financial advice ("you should invest in...", "buy now").
- Keep a neutral, educational tone throughout.

Output format — respond with ONLY a single valid JSON object. No markdown
code fences, no commentary before or after it. Exact shape:
{
  "meta_title": "string, under 60 characters",
  "meta_description": "string, 140-155 characters",
  "excerpt": "string, under 300 characters — a 1-2 sentence teaser",
  "sections": ["<h2>Heading</h2><p>...</p>", "<h2>Heading</h2><p>...</p>...more sections here"]
}
PROMPT;
    }

    private function buildUserPrompt(array $spec): string
    {
        $coins = empty($spec['related_coin_slugs'])
            ? 'none specific'
            : implode(', ', $spec['related_coin_slugs']);

        return "Write a blog article for CryptoInfo with:\n".
            "Title: {$spec['title']}\n".
            "Category: {$spec['category']}\n".
            "Related coins: {$coins}\n\n".
            'Respond with only the JSON object described in the system prompt.';
    }

    /**
     * @return array<string, mixed> Decoded Groq (OpenAI-compatible) chat completions response.
     *
     * @throws ArticleGenerationException
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
            Log::error('ArticleGeneratorService: Groq API call failed', ['message' => $e->getMessage()]);

            throw new ArticleGenerationException('Groq API call failed: '.$e->getMessage(), previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     *
     * @throws ArticleGenerationException
     */
    private function parseResponse(array $response, string $title): array
    {
        if (($response['choices'][0]['finish_reason'] ?? null) === 'length') {
            throw new ArticleGenerationException("Groq response was truncated (max_tokens) for \"{$title}\".");
        }

        $text = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new ArticleGenerationException("Groq response had no text content for \"{$title}\".");
        }

        $text = $this->stripCodeFences($text);
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new ArticleGenerationException("Groq response was not valid JSON for \"{$title}\": ".json_last_error_msg());
        }

        foreach (['meta_title', 'meta_description', 'excerpt', 'sections'] as $key) {
            if (empty($data[$key])) {
                throw new ArticleGenerationException("Groq response missing required field \"{$key}\" for \"{$title}\".");
            }
        }

        if (! is_array($data['sections'])) {
            throw new ArticleGenerationException("Groq response \"sections\" was not an array for \"{$title}\".");
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

    private function resolveCategoryId(?string $slug): ?int
    {
        if (blank($slug)) {
            return null;
        }

        return ArticleCategory::query()->where('slug', $slug)->value('id');
    }

    /**
     * @param array<int, string> $slugs
     * @return array<int, string>
     */
    private function resolveCoinSlugs(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        return Cryptocurrency::query()
            ->whereIn('slug', $slugs)
            ->pluck('slug')
            ->all();
    }
}
