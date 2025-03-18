<?php

namespace App\Filament\Resources\GuideTripResource\Pages;

use App\Filament\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuideTrip extends EditRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
