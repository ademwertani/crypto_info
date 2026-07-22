<?php

namespace App\Filament\Resources\NewsPosts\Tables;

use App\Models\NewsPost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray')
                    ->sortable(),

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
                        'published' => 'Published',
                    ]),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->visible(fn (NewsPost $record): bool => $record->status !== 'published')
                    ->action(function (NewsPost $record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => $record->published_at ?? now(),
                        ]);
                    }),

                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->visible(fn (NewsPost $record): bool => $record->status === 'published')
                    ->action(fn (NewsPost $record) => $record->update(['status' => 'draft'])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
