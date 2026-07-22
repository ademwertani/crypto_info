<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\MoneyPages\MoneyPageResource;
use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Services\SeoAuditor;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SeoTopIssuesTable extends TableWidget
{
    protected static ?string $heading = 'Top 20 pages to fix';

    // Cryptocurrency has no admin CRUD (auto-synced by cron, never
    // hand-edited) — its rows only get a "View" action. Every other content
    // type below has a Filament resource, so those get a real "Edit" link.
    private const EDIT_RESOURCES = [
        'news_post' => NewsPostResource::class,
        'money_page' => MoneyPageResource::class,
        'article' => ArticleResource::class,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => collect(app(SeoAuditor::class)->cached()['pages'])
                ->sortBy('score')
                ->take(20)
                ->values()
                // TextColumn treats an array state as a list (formatStateUsing
                // runs per element, not on the whole array) — flatten to a
                // plain string up front rather than fight that behaviour.
                ->map(fn (array $p) => [...$p, 'issues_label' => $p['issues'] === [] ? '—' : implode(', ', $p['issues'])]))
            ->columns([
                TextColumn::make('label')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('h1_or_title')
                    ->label('Title / H1')
                    ->limit(50),

                TextColumn::make('score')
                    ->suffix('%')
                    ->weight('bold')
                    ->color(fn (int $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('issues_label')
                    ->label('Issues')
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (array $record): string => $record['url'])
                    ->openUrlInNewTab(),

                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencil)
                    ->visible(fn (array $record): bool => array_key_exists($record['type'], self::EDIT_RESOURCES))
                    ->url(fn (array $record): ?string => isset(self::EDIT_RESOURCES[$record['type']])
                        ? self::EDIT_RESOURCES[$record['type']]::getUrl('edit', ['record' => $record['id']])
                        : null),
            ])
            ->paginated(false);
    }
}
