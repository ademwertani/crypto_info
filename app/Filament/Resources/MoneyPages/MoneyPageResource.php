<?php

namespace App\Filament\Resources\MoneyPages;

use App\Filament\Resources\MoneyPages\Pages\CreateMoneyPage;
use App\Filament\Resources\MoneyPages\Pages\EditMoneyPage;
use App\Filament\Resources\MoneyPages\Pages\ListMoneyPages;
use App\Filament\Resources\MoneyPages\Schemas\MoneyPageForm;
use App\Filament\Resources\MoneyPages\Tables\MoneyPagesTable;
use App\Models\MoneyPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MoneyPageResource extends Resource
{
    protected static ?string $model = MoneyPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Money Pages';

    protected static ?string $modelLabel = 'Money page';

    protected static ?string $recordTitleAttribute = 'h1';

    public static function form(Schema $schema): Schema
    {
        return MoneyPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MoneyPagesTable::configure($table);
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
            'index' => ListMoneyPages::route('/'),
            'create' => CreateMoneyPage::route('/create'),
            'edit' => EditMoneyPage::route('/{record}/edit'),
        ];
    }
}
