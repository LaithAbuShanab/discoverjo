<?php

namespace App\Filament\Provider\Resources\ChaletReservationResource\Pages;

use App\Filament\Provider\Resources\ChaletReservationResource;
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
