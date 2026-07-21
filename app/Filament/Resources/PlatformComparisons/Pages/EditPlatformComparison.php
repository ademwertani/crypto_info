<?php

namespace App\Filament\Resources\PlatformComparisons\Pages;

use App\Filament\Resources\PlatformComparisons\PlatformComparisonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlatformComparison extends EditRecord
{
    protected static string $resource = PlatformComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
