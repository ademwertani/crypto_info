<?php

namespace App\Filament\Resources\MoneyPages\Tables;

use App\Models\MoneyPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MoneyPagesTable
{
    // Below this word count the page is unlikely to compete for its target
    // keyword — flagged in the "Quality" column so thin pages get caught
    // before publish, without blocking the mass-production workflow.
    private const MIN_WORDS = 600;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('h1')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state)))
                    ->sortable(),

                TextColumn::make('cluster')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_review' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('quality')
                    ->label('Quality')
                    ->badge()
                    ->state(fn (MoneyPage $record): string => self::isThin($record) ? 'Needs work' : 'OK')
                    ->color(fn (MoneyPage $record): string => self::isThin($record) ? 'danger' : 'success')
                    ->tooltip(fn (MoneyPage $record): string => self::isThin($record)
                        ? 'Missing meta description, or body under '.self::MIN_WORDS.' words'
                        : 'Meta description set and body over '.self::MIN_WORDS.' words'),

                TextColumn::make('views')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'in_review' => 'In review',
                        'published' => 'Published',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'buy_asset' => 'Buy Asset',
                        'best_list' => 'Best List',
                        'exchange_review' => 'Exchange Review',
                        'wallet_review' => 'Wallet Review',
                        'how_to' => 'How To',
                        'comparison' => 'Comparison',
                    ]),
                SelectFilter::make('cluster')
                    ->options(fn () => MoneyPage::query()->whereNotNull('cluster')->distinct()->pluck('cluster', 'cluster')->all()),
            ])
            ->recordActions([
                Action::make('inReview')
                    ->label('Mark in review')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('warning')
                    ->visible(fn (MoneyPage $record): bool => $record->status === 'draft')
                    ->action(fn (MoneyPage $record) => $record->update(['status' => 'in_review'])),

                Action::make('publish')
                    ->label('Publish')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('success')
                    ->visible(fn (MoneyPage $record): bool => $record->status !== 'published')
                    ->action(function (MoneyPage $record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => $record->published_at ?? now(),
                        ]);
                    }),

                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->visible(fn (MoneyPage $record): bool => $record->status === 'published')
                    ->action(fn (MoneyPage $record) => $record->update(['status' => 'draft'])),

                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->color('gray')
                    ->url(fn (MoneyPage $record): string => route('guides.preview', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function isThin(MoneyPage $record): bool
    {
        if (blank($record->meta_description)) {
            return true;
        }

        return str_word_count(strip_tags((string) $record->body_html)) < self::MIN_WORDS;
    }
}
