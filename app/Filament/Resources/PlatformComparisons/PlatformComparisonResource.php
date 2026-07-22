<?php

namespace App\Filament\Resources\PlatformComparisons;

use App\Filament\Resources\PlatformComparisons\Pages\CreatePlatformComparison;
use App\Filament\Resources\PlatformComparisons\Pages\EditPlatformComparison;
use App\Filament\Resources\PlatformComparisons\Pages\ListPlatformComparisons;
use App\Filament\Resources\PlatformComparisons\Schemas\PlatformComparisonForm;
use App\Filament\Resources\PlatformComparisons\Tables\PlatformComparisonsTable;
use App\Models\PlatformComparison;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlatformComparisonResource extends Resource
{
    protected static ?string $model = PlatformComparison::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Platforms';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Platform Comparisons';

    protected static ?string $modelLabel = 'Platform comparison';

    public static function form(Schema $schema): Schema
    {
        return PlatformComparisonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlatformComparisonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatformComparisons::route('/'),
            'create' => CreatePlatformComparison::route('/create'),
            'edit' => EditPlatformComparison::route('/{record}/edit'),
        ];
    }
}
