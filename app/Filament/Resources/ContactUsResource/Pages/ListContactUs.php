<?php

namespace App\Filament\Resources\ContactUsResource\Pages;

use App\Filament\Resources\ContactUsResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListContactUs extends ListRecords
{
    protected static string $resource = ContactUsResource::class;

    protected function getHeaderActions(): array
    {
        return [

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
