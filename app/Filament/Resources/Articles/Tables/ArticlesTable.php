<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image_url')
                    ->label(''),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('article_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->visible(fn (Article $record): bool => $record->status !== 'published')
                    ->action(function (Article $record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => $record->published_at ?? now(),
                        ]);
                    }),

                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->visible(fn (Article $record): bool => $record->status === 'published')
                    ->action(fn (Article $record) => $record->update(['status' => 'draft'])),

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
