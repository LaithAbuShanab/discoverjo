<?php

namespace App\Filament\Resources\VolunteeringResource\Pages;

use App\Filament\Resources\VolunteeringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVolunteerings extends ListRecords
{
    use ListRecords\Concerns\Translatable;
    protected static string $resource = VolunteeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
