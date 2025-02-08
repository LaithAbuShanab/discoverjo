<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

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
            'active'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 1)),
            'Inactive'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 0)),
            'guide'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('is_guide', true)),
            'First login'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 2)),
            'Inactive by admin'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 3)),
            'deactivate by user'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('status', 4)),
        ];
    }
}
