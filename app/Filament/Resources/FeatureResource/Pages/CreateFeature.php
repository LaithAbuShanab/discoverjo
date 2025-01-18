<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFeature extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
