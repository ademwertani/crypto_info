<?php

namespace App\Filament\Resources\AdFormats;

use App\Filament\Resources\AdFormats\Pages\CreateAdFormat;
use App\Filament\Resources\AdFormats\Pages\EditAdFormat;
use App\Filament\Resources\AdFormats\Pages\ListAdFormats;
use App\Filament\Resources\AdFormats\Schemas\AdFormatForm;
use App\Filament\Resources\AdFormats\Tables\AdFormatsTable;
use App\Models\AdFormat;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdFormatResource extends Resource
{
    protected static ?string $model = AdFormat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Advertise';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Ad Formats';

    protected static ?string $modelLabel = 'Ad Format';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdFormatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdFormatsTable::configure($table);
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
            'index' => ListAdFormats::route('/'),
            'create' => CreateAdFormat::route('/create'),
            'edit' => EditAdFormat::route('/{record}/edit'),
        ];
    }
}
