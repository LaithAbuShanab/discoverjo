<?php

namespace App\Filament\Resources\VolunteeringResource\Pages;

use App\Filament\Resources\VolunteeringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVolunteering extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = VolunteeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
