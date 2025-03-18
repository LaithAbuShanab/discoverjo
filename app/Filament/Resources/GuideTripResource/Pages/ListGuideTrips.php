<?php

namespace App\Filament\Resources\GuideTripResource\Pages;

use App\Filament\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuideTrips extends ListRecords
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
