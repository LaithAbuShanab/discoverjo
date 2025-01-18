<?php

namespace App\Filament\Resources\PopularPlaceResource\Pages;

use App\Filament\Resources\PopularPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopularPlaces extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = PopularPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
