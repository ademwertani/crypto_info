<?php

namespace App\Filament\Resources\AdvertiserLeads\Pages;

use App\Filament\Resources\AdvertiserLeads\AdvertiserLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListAdvertiserLeads extends ListRecords
{
    protected static string $resource = AdvertiserLeadResource::class;

    protected function getHeaderActions(): array
    {
        // No CreateAction: leads only ever originate from the public
        // /advertise form, never created by hand in the admin panel.
        return [];
    }
}
