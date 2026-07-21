<?php

namespace App\Filament\Widgets;

use App\Services\SeoAuditor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeoHealthOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = app(SeoAuditor::class)->cached()['stats'];

        return [
            Stat::make('SEO health score', $stats['average_score'].'%')
                ->description('Average across all indexable pages')
                ->color($this->scoreColor((float) $stats['average_score'])),

            Stat::make('Pages audited', $stats['total'])
                ->description('Crypto, guides, blog & news'),

            Stat::make('Pages with issues', $stats['pages_with_issues'])
                ->description('Out of '.$stats['total'].' pages')
                ->color($stats['pages_with_issues'] > 0 ? 'warning' : 'success'),

            Stat::make('Broken internal links', $stats['broken_internal_links'])
                ->description('Best-effort — internal links only')
                ->color($stats['broken_internal_links'] > 0 ? 'danger' : 'success'),
        ];
    }

    private function scoreColor(float $score): string
    {
        return match (true) {
            $score >= 90 => 'success',
            $score >= 70 => 'warning',
            default => 'danger',
        };
    }
}
