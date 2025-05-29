<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

class ListPlaces extends ListRecords
{

    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            PlaceResource\Widgets\MostViewedPlacesChart::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'active'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 1)),
            'Inactive'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 0)),
        ];
    }
}
