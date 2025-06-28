<?php

namespace App\Filament\provider\Resources\GuideTripResource\Pages;

use App\Filament\provider\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuideTrips extends ListRecords
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('panel.guide.create')),
        ];
    }
}
