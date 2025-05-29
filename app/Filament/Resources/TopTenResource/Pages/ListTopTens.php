<?php

namespace App\Filament\Resources\TopTenResource\Pages;

use App\Filament\Resources\TopTenResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\ListRecords;


class ListTopTens extends ListRecords
{
    protected static string $resource = TopTenResource::class;

    protected function getHeaderActions(): array
    {
        $limitTen = TopTenResource::getModel()::query()->limit(10)->get();
        if (count($limitTen) < 10) {
            return [
                Actions\CreateAction::make(),

            ];
        } else {
            return [];
        }
    }
    protected function getHeaderWidgets(): array
    {
        return [
            TopTenResource\Widgets\topTenState::class,
        ];
    }

}
