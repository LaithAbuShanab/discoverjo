<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlaces extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
