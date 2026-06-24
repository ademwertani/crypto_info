<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsApiService
{
    // Uses CryptoPanic public RSS feed (no API key required for basic usage)
    private const RSS_URL = 'https://cryptopanic.com/news/rss/';

    public function fetchLatest(int $limit = 30): array
    {
        try {
            $response = Http::timeout(15)->get(self::RSS_URL);

            if (! $response->ok()) {
                Log::warning('CryptoPanic RSS returned ' . $response->status());
                return [];
            }

            return $this->parseRss($response->body(), $limit);
        } catch (\Throwable $e) {
            Log::error('NewsApiService: ' . $e->getMessage());
            return [];
        }
    }

    private function parseRss(string $xml, int $limit): array
    {
        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);

        if ($feed === false) {
            return [];
        }

        $items = [];

        foreach ($feed->channel->item as $item) {
            if (count($items) >= $limit) break;

            $title = (string) $item->title;
            $url   = (string) $item->link;
            $date  = (string) $item->pubDate;
            $desc  = strip_tags((string) ($item->description ?? ''));

            if (empty($title) || empty($url)) continue;

            $slug = Str::slug($title);
            if (strlen($slug) > 191) {
                $slug = substr($slug, 0, 180) . '-' . substr(md5($url), 0, 8);
            }

            $items[] = [
                'title'        => $title,
                'slug'         => $slug,
                'summary'      => $desc ? substr($desc, 0, 500) : null,
                'url'          => $url,
                'source'       => 'CryptoPanic',
                'image_url'    => null,
                'coin_slugs'   => null,
                'sentiment'    => 'neutral',
                'published_at' => $date ? date('Y-m-d H:i:s', strtotime($date)) : now()->toDateTimeString(),
            ];
        }

        return $items;
    }
}
