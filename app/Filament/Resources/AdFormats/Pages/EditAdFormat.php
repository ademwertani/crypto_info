<?php

namespace App\Filament\Resources\AdFormats\Pages;

use App\Filament\Resources\AdFormats\AdFormatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdFormat extends EditRecord
{
    protected static string $resource = AdFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
