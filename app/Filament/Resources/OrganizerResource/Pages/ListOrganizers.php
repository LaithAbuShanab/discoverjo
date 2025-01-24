<?php

namespace App\Filament\Resources\OrganizerResource\Pages;

use App\Filament\Resources\OrganizerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizers extends ListRecords
{
    use ListRecords\Concerns\Translatable;
    protected static string $resource = OrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
