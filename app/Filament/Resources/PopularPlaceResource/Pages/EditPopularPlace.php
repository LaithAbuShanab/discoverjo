<?php

namespace App\Filament\Resources\PopularPlaceResource\Pages;

use App\Filament\Resources\PopularPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPopularPlace extends EditRecord
{
    protected static string $resource = PopularPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
