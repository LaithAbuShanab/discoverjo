<?php

namespace App\Filament\Resources\TopTenResource\Pages;

use App\Filament\Resources\TopTenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopTens extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = TopTenResource::class;

    protected function getHeaderActions(): array
    {
        $limitTen = TopTenResource::getModel()::query()->limit(10)->get();
        if (count($limitTen) < 10) {
            return [
                Actions\CreateAction::make(),
                Actions\LocaleSwitcher::make(),
            ];
        } else {
            return [];
        }
    }
}
