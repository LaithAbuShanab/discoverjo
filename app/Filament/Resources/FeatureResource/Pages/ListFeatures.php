<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeatures extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
