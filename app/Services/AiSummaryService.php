<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AiSummaryService
{
    public function summarizeNews(string $title, ?string $body = null): ?string
    {
        if (empty(config('openai.api_key')) || config('openai.api_key') === 'your-openai-key-here') {
            return null;
        }

        try {
            $content = $title;
            if ($body) {
                $content .= "\n\n" . substr($body, 0, 2000);
            }

            $response = OpenAI::chat()->create([
                'model'       => 'gpt-4o-mini',
                'max_tokens'  => 120,
                'temperature' => 0.4,
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a crypto journalist. Write a concise 2-sentence summary of the news article for a market overview card. Be factual and neutral.'],
                    ['role' => 'user',   'content' => $content],
                ],
            ]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Throwable $e) {
            Log::warning('AiSummaryService: ' . $e->getMessage());
            return null;
        }
    }

    public function whyPriceMoved(string $coinName, float $change24h, ?string $newsContext = null): ?string
    {
        if (empty(config('openai.api_key')) || config('openai.api_key') === 'your-openai-key-here') {
            return null;
        }

        try {
            $direction = $change24h >= 0 ? 'up' : 'down';
            $absChange = abs($change24h);
            $prompt    = "Why is {$coinName} {$direction} {$absChange}% today?";
            if ($newsContext) {
                $prompt .= "\n\nRecent news context:\n" . substr($newsContext, 0, 1000);
            }

            $response = OpenAI::chat()->create([
                'model'       => 'gpt-4o-mini',
                'max_tokens'  => 200,
                'temperature' => 0.5,
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a crypto market analyst. Explain briefly why a cryptocurrency might be moving in the given direction based on market context. Be concise, factual, and note that this is not financial advice.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Throwable $e) {
            Log::warning('AiSummaryService: ' . $e->getMessage());
            return null;
        }
    }
}
