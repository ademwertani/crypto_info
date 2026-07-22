<?php

namespace App\Console\Commands;

use App\Exceptions\NewsPostGenerationException;
use App\Models\NewsPost;
use App\Services\NewsApiService;
use App\Services\NewsPostGeneratorService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

#[Signature('news:generate {--limit=10} {--dry-run}')]
#[Description('Draft NewsPost articles from real RSS news items via the Groq API — always drafts, expands only real facts, never invents news')]
class GenerateNewsPosts extends Command
{
    // Hard ceiling regardless of what --limit is passed — cost control
    // guardrail against an accidental --limit=500.
    private const MAX_LIMIT = 30;

    // Fetch more RSS items than the limit so there's still something left
    // to generate after skipping articles already drafted.
    private const RSS_FETCH_COUNT = 40;

    private const DELAY_SECONDS_BETWEEN_CALLS = 1;

    public function handle(NewsApiService $api, NewsPostGeneratorService $generator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && blank(config('services.groq.api_key'))) {
            $this->error('GROQ_API_KEY is not set — add it to .env before running this command.');

            return self::FAILURE;
        }

        $limit = min(max((int) $this->option('limit'), 1), self::MAX_LIMIT);

        $items = $api->fetchLatest(limit: self::RSS_FETCH_COUNT);

        if (empty($items)) {
            $this->warn('No RSS items returned.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $failed = 0;
        $rows = [];

        foreach ($items as $item) {
            if ($generated >= $limit) {
                break;
            }

            if (NewsPost::where('source_url', $item['url'])->exists()) {
                $skipped++;
                $rows[] = [$item['title'], $item['source'] ?? '—', 'skipped (already drafted)'];

                continue;
            }

            if ($dryRun) {
                $generated++;
                $rows[] = [$item['title'], $item['source'] ?? '—', 'would generate'];

                continue;
            }

            try {
                $data = $generator->generate($item);

                NewsPost::create([...$data,
                    // title/slug are both varchar(191) (see the
                    // meta_description incident) — real headlines have
                    // stayed well under that so far, but nothing enforced
                    // it, so an unusually long one would crash the batch
                    // the same way.
                    'title' => Str::limit($item['title'], 191, ''),
                    'status' => 'draft',
                    'published_at' => $item['published_at'] ?? now(),
                    'source_url' => $item['url'],
                    'source_name' => $item['source'] ?? null,
                ]);

                $generated++;
                $rows[] = [$item['title'], $item['source'] ?? '—', 'generated (draft)'];
            } catch (NewsPostGenerationException|QueryException $e) {
                $failed++;
                $rows[] = [$item['title'], $item['source'] ?? '—', 'FAILED'];
                $this->warn("Failed: {$item['title']} — {$e->getMessage()}");
            }

            if (! app()->environment('testing')) {
                sleep(self::DELAY_SECONDS_BETWEEN_CALLS);
            }
        }

        $this->table(['Title', 'Source', 'Result'], $rows);
        $this->info("Generated: {$generated}  Skipped: {$skipped}  Failed: {$failed}");

        return self::SUCCESS;
    }
}
