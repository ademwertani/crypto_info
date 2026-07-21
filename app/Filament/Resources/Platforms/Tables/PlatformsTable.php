<?php

namespace App\Filament\Resources\Platforms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlatformsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('best_for')
                    ->label('Best for')
                    ->badge()
                    ->color('info'),

                TextColumn::make('hq_country')
                    ->label('HQ')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('requires_kyc')
                    ->label('KYC')
                    ->boolean(),

                IconColumn::make('supports_cards')
                    ->label('Cards')
                    ->boolean(),

                TextColumn::make('data_verified_at')
                    ->label('Fees verified')
                    ->dateTime('M d, Y')
                    ->placeholder('Not verified')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('type')->options([
                    'exchange' => 'Exchange',
                    'wallet' => 'Wallet',
                ]),
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                ]),
            ])
            ->recordActions([
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
