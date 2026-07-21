<?php

namespace App\Filament\Resources\AdvertiserLeads;

use App\Filament\Resources\AdvertiserLeads\Pages\ListAdvertiserLeads;
use App\Filament\Resources\AdvertiserLeads\Schemas\AdvertiserLeadForm;
use App\Filament\Resources\AdvertiserLeads\Tables\AdvertiserLeadsTable;
use App\Models\AdvertiserLead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertiserLeadResource extends Resource
{
    protected static ?string $model = AdvertiserLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $navigationLabel = 'Advertiser Leads';

    protected static ?string $modelLabel = 'Advertiser Lead';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Only used by the read-only ViewAction in the table below — leads are
     * never created or edited by hand, only submitted through /advertise
     * and triaged via the status SelectColumn.
     */
    public static function form(Schema $schema): Schema
    {
        return AdvertiserLeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertiserLeadsTable::configure($table);
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
            'index' => ListAdvertiserLeads::route('/'),
        ];
    }
}
