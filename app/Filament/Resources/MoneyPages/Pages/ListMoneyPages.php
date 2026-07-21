<?php

namespace App\Filament\Resources\MoneyPages\Pages;

use App\Filament\Resources\MoneyPages\MoneyPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMoneyPages extends ListRecords
{
    protected static string $resource = MoneyPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
