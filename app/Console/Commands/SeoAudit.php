<?php

namespace App\Console\Commands;

use App\Services\SeoAuditor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('seo:audit')]
#[Description('Audit meta titles/descriptions, H1s, thin content, image alts and internal links across all indexable content')]
class SeoAudit extends Command
{
    private const TOP_N = 20;

    public function handle(SeoAuditor $auditor): int
    {
        $this->info('Auditing indexable content (crypto pages, guides, blog, news)…');

        // Always a fresh pass — unlike the dashboard widgets, which read
        // SeoAuditor::cached() so /admin doesn't re-audit on every load.
        $result = $auditor->run();

        /** @var Collection $pages */
        $pages = $result['pages'];
        /** @var Collection $errors */
        $errors = $result['errors'];
        $stats = $result['stats'];

        if ($pages->isEmpty()) {
            $this->warn('No indexable content found to audit.');

            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'Pages', 'Avg score', 'With issues'],
            collect($stats['by_type'])->map(fn (array $row) => [
                $row['label'], $row['count'], $row['average_score'].'%', $row['pages_with_issues'],
            ])->all()
        );

        $this->newLine();
        $this->line(sprintf(
            'Overall: <fg=cyan>%d</> pages audited, average score <fg=cyan>%s%%</>, <fg=cyan>%d</> pages with issues, <fg=cyan>%d</> broken internal links.',
            $stats['total'],
            $stats['average_score'],
            $stats['pages_with_issues'],
            $stats['broken_internal_links'],
        ));

        $this->newLine();
        $this->line('Top '.self::TOP_N.' pages to fix:');
        $this->table(
            ['Type', 'Title / H1', 'Score', 'Issues'],
            $pages->sortBy('score')->take(self::TOP_N)->map(fn (array $p) => [
                $p['label'],
                Str::limit($p['h1_or_title'], 50),
                $p['score'].'%',
                $p['issues'] === [] ? '—' : implode(', ', $p['issues']),
            ])->all()
        );

        if ($errors->isNotEmpty()) {
            $this->newLine();
            $this->warn("{$errors->count()} page(s) skipped due to an unexpected error:");
            foreach ($errors as $error) {
                $this->line(" - {$error['type']} #{$error['id']}: {$error['message']}");
            }
        }

        $path = $this->exportCsv($pages);
        $this->newLine();
        $this->info("Full CSV report ({$pages->count()} pages) written to: {$path}");

        return self::SUCCESS;
    }

    private function exportCsv(Collection $pages): string
    {
        $filename = 'seo-audits/seo-audit-'.now()->format('Y-m-d_His').'.csv';

        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, [
            'type', 'id', 'slug', 'url', 'h1_or_title',
            'meta_title_length', 'meta_description_length', 'word_count', 'score', 'issues',
        ]);

        foreach ($pages as $page) {
            fputcsv($handle, [
                $page['type'],
                $page['id'],
                $page['slug'],
                $page['url'],
                $page['h1_or_title'],
                $page['meta_title_length'],
                $page['meta_description_length'],
                $page['word_count'],
                $page['score'],
                implode(';', $page['issues']),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($filename, $csv);

        return Storage::disk('local')->path($filename);
    }
}
