<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'seen'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('seen_status', 1)),
            'unseen'=>Tab::make()->modifyQueryUsing(fn(Builder $query)=> $query->where('seen_status', 0)),
        ];
    }
}
