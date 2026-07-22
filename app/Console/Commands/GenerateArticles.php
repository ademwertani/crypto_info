<?php

namespace App\Console\Commands;

use App\Exceptions\ArticleGenerationException;
use App\Models\Article;
use App\Services\ArticleGeneratorService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('blog:generate {--category=} {--limit=5} {--dry-run}')]
#[Description('Generate draft Blog Article content via the Groq API — always drafts, never publishes')]
class GenerateArticles extends Command
{
    // Hard ceiling regardless of what --limit is passed — cost control
    // guardrail against an accidental --limit=500.
    private const MAX_LIMIT = 50;

    private const DELAY_SECONDS_BETWEEN_CALLS = 1;

    public function handle(ArticleGeneratorService $generator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && blank(config('services.groq.api_key'))) {
            $this->error('GROQ_API_KEY is not set — add it to .env before running this command.');

            return self::FAILURE;
        }

        $category = $this->option('category');
        $limit = min(max((int) $this->option('limit'), 1), self::MAX_LIMIT);

        $articles = collect(config('blog_pipeline.articles', []));

        if ($category) {
            $articles = $articles->where('category', $category);
        }

        if ($articles->isEmpty()) {
            $this->warn('No pipeline entries match the given filters.');

            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;
        $failed = 0;
        $rows = [];

        foreach ($articles as $spec) {
            if ($generated >= $limit) {
                break;
            }

            $slug = Str::slug($spec['title']);

            if (Article::where('slug', $slug)->exists()) {
                $skipped++;
                $rows[] = [$spec['title'], $spec['category'] ?? '—', 'skipped (exists)'];

                continue;
            }

            if ($dryRun) {
                $generated++;
                $rows[] = [$spec['title'], $spec['category'] ?? '—', 'would generate'];

                continue;
            }

            try {
                $data = $generator->generate($spec);
                Article::create([...$data, 'slug' => $slug, 'status' => 'draft']);
                $generated++;
                $rows[] = [$spec['title'], $spec['category'] ?? '—', 'generated (draft)'];
            } catch (ArticleGenerationException $e) {
                $failed++;
                $rows[] = [$spec['title'], $spec['category'] ?? '—', 'FAILED'];
                $this->warn("Failed: {$spec['title']} — {$e->getMessage()}");
            }

            if (! app()->environment('testing')) {
                sleep(self::DELAY_SECONDS_BETWEEN_CALLS);
            }
        }

        $this->table(['Title', 'Category', 'Result'], $rows);
        $this->info("Generated: {$generated}  Skipped: {$skipped}  Failed: {$failed}");

        return self::SUCCESS;
    }
}
