<?php

namespace App\Filament\Host\Resources\ChaletReservationResource\Pages;

use App\Filament\Host\Resources\ChaletReservationResource;
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
