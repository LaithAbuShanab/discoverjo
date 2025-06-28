<?php

namespace App\Filament\Provider\Resources\ChaletReservationResource\Pages;

use App\Filament\Provider\Resources\ChaletReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChaletReservations extends ListRecords
{
    protected static string $resource = ChaletReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
