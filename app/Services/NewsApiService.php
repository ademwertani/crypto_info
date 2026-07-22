<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsApiService
{
    // CryptoPanic's public /news/rss/ endpoint now serves their Vue.js app
    // shell instead of XML (returns 200 but isn't a feed anymore) — these
    // two publishers still serve real RSS 2.0 with no API key required.
    private const FEEDS = [
        'CoinDesk' => 'https://www.coindesk.com/arc/outboundfeeds/rss/',
        'CoinTelegraph' => 'https://cointelegraph.com/rss',
    ];

    public function fetchLatest(int $limit = 30): array
    {
        $items = [];

        foreach (self::FEEDS as $source => $url) {
            try {
                $response = Http::timeout(15)->get($url);

                if (! $response->ok()) {
                    Log::warning("{$source} RSS returned ".$response->status());
                    continue;
                }

                $items = [...$items, ...$this->parseRss($response->body(), $source)];
            } catch (\Throwable $e) {
                Log::error("NewsApiService ({$source}): ".$e->getMessage());
            }
        }

        usort($items, fn ($a, $b) => strcmp($b['published_at'], $a['published_at']));

        return array_slice($items, 0, $limit);
    }

    private function parseRss(string $xml, string $source): array
    {
        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);

        if ($feed === false) {
            return [];
        }

        $items = [];

        foreach ($feed->channel->item as $item) {
            $title = (string) $item->title;
            $url   = (string) $item->link;
            $date  = (string) $item->pubDate;
            $desc  = strip_tags((string) ($item->description ?? ''));

            if (empty($title) || empty($url)) {
                continue;
            }

            $slug = Str::slug($title);
            if (strlen($slug) > 191) {
                $slug = substr($slug, 0, 180) . '-' . substr(md5($url), 0, 8);
            }

            $items[] = [
                'title'        => $title,
                'slug'         => $slug,
                'summary'      => $desc ? substr($desc, 0, 500) : null,
                'url'          => $url,
                'source'       => $source,
                'image_url'    => null,
                'coin_slugs'   => null,
                'sentiment'    => 'neutral',
                'published_at' => $date ? date('Y-m-d H:i:s', strtotime($date)) : now()->toDateTimeString(),
            ];
        }

        return $items;
    }
}
