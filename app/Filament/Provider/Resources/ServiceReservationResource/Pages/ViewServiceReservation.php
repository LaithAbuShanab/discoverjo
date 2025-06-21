<?php

namespace App\Filament\Provider\Resources\ServiceReservationResource\Pages;

use App\Filament\Provider\Resources\ServiceReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceReservation extends ViewRecord
{
    protected static string $resource = ServiceReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
