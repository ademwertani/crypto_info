<?php

namespace App\Filament\Resources\MoneyPages\Pages;

use App\Filament\Resources\MoneyPages\MoneyPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMoneyPage extends EditRecord
{
    protected static string $resource = MoneyPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
