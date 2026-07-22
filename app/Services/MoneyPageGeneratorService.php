<?php

namespace App\Services;

use App\Exceptions\MoneyPageGenerationException;
use App\Models\Cryptocurrency;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MoneyPageGeneratorService
{
    private const MAX_TOKENS = 4096;

    private const TIMEOUT_SECONDS = 60;

    private const MAX_RETRIES = 3;

    /**
     * @param array{title: string, type: string, cluster: string, related_coin_slugs?: array<int, string>} $spec
     * @return array<string, mixed> Ready to spread into MoneyPage::create().
     *
     * @throws MoneyPageGenerationException
     */
    public function generate(array $spec): array
    {
        $response = $this->callGroq(
            $this->buildSystemPrompt($spec['type']),
            $this->buildUserPrompt($spec),
        );

        $data = $this->parseResponse($response, $spec['title']);

        return [
            'h1' => $spec['title'],
            'type' => $spec['type'],
            'cluster' => $spec['cluster'],
            'meta_title' => Str::limit((string) $data['meta_title'], 60, ''),
            // Column is varchar(191) — AppServiceProvider sets
            // Builder::defaultStringLength(191), not Laravel's usual 255
            // default. The prompt asks for 140-155 chars but that's not
            // enforced by the model, so hard-cap it here too (the descLen
            // warning below is informational only, it never stopped an
            // oversized value from reaching the DB before this).
            'meta_description' => Str::limit((string) $data['meta_description'], 191, ''),
            'intro_html' => (string) $data['intro_html'],
            'body_html' => (string) $data['body_html'],
            'faq' => array_values((array) $data['faq']),
            'cta_config' => $this->sanitizeCtaConfig((array) ($data['cta_labels'] ?? [])),
            'related_coin_ids' => $this->resolveCoinIds($spec['related_coin_slugs'] ?? []),
            'author' => 'CryptoInfo AI Draft',
        ];
    }

    private function buildSystemPrompt(string $type): string
    {
        $prompt = <<<'PROMPT'
You are a professional crypto content writer for CryptoInfo, a cryptocurrency market data website.

Write 100% original, factual, genuinely useful content. Never copy or closely paraphrase any
existing source.

Structure:
- Use H2 and H3 headings to organize the article body.
- Total length: 900 to 1300 words across the intro and body combined.
- Open with a short, engaging 2-3 sentence introduction (returned separately as "intro_html").
- Include a FAQ section with exactly 4 to 5 question/answer pairs.

ABSOLUTE PROHIBITIONS — content violating any of these is unacceptable:
- No promises or guarantees of profit or gains.
- No affirmative price predictions ("X will reach $Y", "X is going to moon").
- No personalized financial advice ("you should invest in...", "buy now").
- Keep a neutral, educational tone throughout.

Leave room for call-to-action placements, but NEVER invent or include a URL. CTAs are represented
only as {label, network, placement} — the real link is added by a human editor before publishing.

Output format — respond with ONLY a single valid JSON object. No markdown code fences, no
commentary before or after it. Exact shape:
{
  "meta_title": "string, under 60 characters",
  "meta_description": "string, 140-155 characters",
  "intro_html": "1-2 short paragraphs of HTML (<p> tags only, no heading)",
  "body_html": "the full article body as HTML using <h2>/<h3>/<p>/<ul>/<ol>, no <h1>",
  "faq": [{"q": "question", "a": "answer"}],
  "cta_labels": [{"label": "short CTA text", "network": "binance|bybit|okx|other", "placement": "short tag"}]
}
PROMPT;

        if ($type === 'buy_asset') {
            $prompt .= "\n\nThis is a \"buy_asset\" page: body_html MUST use exactly these H2 sections, ".
                'in this order: "How to Buy", "Where to Buy", "Fees to Watch", "Security". '.
                'The FAQ stays in the separate "faq" field, not as a body H2.';
        }

        return $prompt;
    }

    private function buildUserPrompt(array $spec): string
    {
        $coins = empty($spec['related_coin_slugs'])
            ? 'none specific'
            : implode(', ', $spec['related_coin_slugs']);

        return "Write a page for CryptoInfo with:\n".
            "Title: {$spec['title']}\n".
            "Type: {$spec['type']}\n".
            "Cluster/topic: {$spec['cluster']}\n".
            "Related coins: {$coins}\n\n".
            'Respond with only the JSON object described in the system prompt.';
    }

    /**
     * @return array<string, mixed> Decoded Groq (OpenAI-compatible) chat completions response.
     *
     * @throws MoneyPageGenerationException
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
            Log::error('MoneyPageGeneratorService: Groq API call failed', ['message' => $e->getMessage()]);

            throw new MoneyPageGenerationException('Groq API call failed: '.$e->getMessage(), previous: $e);
        }
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     *
     * @throws MoneyPageGenerationException
     */
    private function parseResponse(array $response, string $title): array
    {
        if (($response['choices'][0]['finish_reason'] ?? null) === 'length') {
            throw new MoneyPageGenerationException("Groq response was truncated (max_tokens) for \"{$title}\".");
        }

        $text = $response['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new MoneyPageGenerationException("Groq response had no text content for \"{$title}\".");
        }

        $text = $this->stripCodeFences($text);
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new MoneyPageGenerationException("Groq response was not valid JSON for \"{$title}\": ".json_last_error_msg());
        }

        foreach (['meta_title', 'meta_description', 'intro_html', 'body_html', 'faq'] as $key) {
            if (empty($data[$key])) {
                throw new MoneyPageGenerationException("Groq response missing required field \"{$key}\" for \"{$title}\".");
            }
        }

        $faqCount = is_array($data['faq']) ? count($data['faq']) : 0;
        if ($faqCount < 4 || $faqCount > 5) {
            Log::warning("MoneyPageGeneratorService: FAQ has {$faqCount} items (expected 4-5) for \"{$title}\" — keeping anyway.");
        }

        $descLen = mb_strlen((string) $data['meta_description']);
        if ($descLen < 140 || $descLen > 155) {
            Log::warning("MoneyPageGeneratorService: meta_description length {$descLen} outside 140-155 for \"{$title}\" — keeping anyway.");
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

    /**
     * @param array<int, mixed> $ctaLabels
     * @return array<int, array{label: string, network: string, placement: string, href: string, coin: string}>
     */
    private function sanitizeCtaConfig(array $ctaLabels): array
    {
        return collect($ctaLabels)
            ->filter(fn ($item) => is_array($item) && filled($item['label'] ?? null))
            ->map(fn ($item) => [
                'label' => (string) $item['label'],
                'network' => (string) ($item['network'] ?? 'other'),
                'placement' => (string) ($item['placement'] ?? 'guide_cta'),
                // Never trust an AI-generated URL — left blank so the CTA
                // repeater's required "href" field forces a human to fill
                // in the real affiliate link before this page can be saved.
                'href' => '',
                'coin' => '',
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $slugs
     * @return array<int, int>
     */
    private function resolveCoinIds(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        return Cryptocurrency::query()
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->all();
    }
}
