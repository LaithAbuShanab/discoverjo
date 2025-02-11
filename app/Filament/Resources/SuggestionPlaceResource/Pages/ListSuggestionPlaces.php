<?php

namespace App\Filament\Resources\SuggestionPlaceResource\Pages;

use App\Filament\Resources\SuggestionPlaceResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSuggestionPlaces extends ListRecords
{
    protected static string $resource = SuggestionPlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'seen'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 1)),
            'unseen'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 0)),
        ];
    }
}
