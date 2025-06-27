<?php

namespace App\Filament\Host\Resources\ChaletReservationResource\Pages;

use App\Filament\Host\Resources\ChaletReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewChaletReservation extends ViewRecord
{
    protected static string $resource = ChaletReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
