<?php

namespace App\Filament\Resources\AdFormats\Pages;

use App\Filament\Resources\AdFormats\AdFormatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdFormats extends ListRecords
{
    protected static string $resource = AdFormatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
