<?php

namespace App\Filament\Resources\PlatformComparisons\Pages;

use App\Filament\Resources\PlatformComparisons\PlatformComparisonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlatformComparisons extends ListRecords
{
    protected static string $resource = PlatformComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
