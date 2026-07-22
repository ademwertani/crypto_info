<?php

namespace App\Console\Commands;

use App\Exceptions\MoneyPageGenerationException;
use App\Models\MoneyPage;
use App\Services\MoneyPageGeneratorService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

#[Signature('pages:generate {--cluster=} {--limit=5} {--dry-run}')]
#[Description('Generate draft MoneyPage content via the Groq API — always drafts, never publishes')]
class GeneratePages extends Command
{
    // Hard ceiling regardless of what --limit is passed — cost control
    // guardrail against an accidental --limit=500.
    private const MAX_LIMIT = 50;

    private const DELAY_SECONDS_BETWEEN_CALLS = 1;

    public function handle(MoneyPageGeneratorService $generator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && blank(config('services.groq.api_key'))) {
            $this->error('GROQ_API_KEY is not set — add it to .env before running this command.');

            return self::FAILURE;
        }

        $cluster = $this->option('cluster');
        $limit = min(max((int) $this->option('limit'), 1), self::MAX_LIMIT);

        $pages = collect(config('money_pages_pipeline.pages', []));

        if ($cluster) {
            $pages = $pages->where('cluster', $cluster);
        }

        if ($pages->isEmpty()) {
            $this->warn('No pipeline entries match the given filters.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $failed = 0;
        $rows = [];

        foreach ($pages as $spec) {
            if ($generated >= $limit) {
                break;
            }

            $slug = Str::slug($spec['title']);

            if (MoneyPage::where('slug', $slug)->exists()) {
                $skipped++;
                $rows[] = [$spec['title'], $spec['cluster'], 'skipped (exists)'];

                continue;
            }

            if ($dryRun) {
                $generated++;
                $rows[] = [$spec['title'], $spec['cluster'], 'would generate'];

                continue;
            }

            try {
                $data = $generator->generate($spec);
                MoneyPage::create([...$data, 'slug' => $slug, 'status' => 'draft']);
                $generated++;
                $rows[] = [$spec['title'], $spec['cluster'], 'generated (draft)'];
            } catch (MoneyPageGenerationException|QueryException $e) {
                $failed++;
                $rows[] = [$spec['title'], $spec['cluster'], 'FAILED'];
                $this->warn("Failed: {$spec['title']} — {$e->getMessage()}");
            }

            if (! app()->environment('testing')) {
                sleep(self::DELAY_SECONDS_BETWEEN_CALLS);
            }
        }

        $this->table(['Title', 'Cluster', 'Result'], $rows);
        $this->info("Generated: {$generated}  Skipped: {$skipped}  Failed: {$failed}");

        return self::SUCCESS;
    }
}
