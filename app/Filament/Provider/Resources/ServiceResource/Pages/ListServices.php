<?php

namespace App\Filament\Provider\Resources\ServiceResource\Pages;

use App\Filament\Provider\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('panel.provider.create'))
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),

            'active' => Tab::make()
                ->badge(
                    \App\Models\Service::whereHas('serviceBookings', function (Builder $query) {
                        $query->where('available_end_date', '>=', now());
                    })->where('provider_type', 'App\Models\User')->where('provider_id', auth()->id())->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('serviceBookings', function (Builder $q) {
                        $q->where('available_end_date', '>=', now());
                    });
                }),


            'inactive' => Tab::make()
                ->badge(
                    \App\Models\Service::whereDoesntHave('serviceBookings', function (Builder $query) {
                        $query->where('available_end_date', '>=', now());
                    })->where('provider_type', 'App\Models\User')->where('provider_id', auth()->id())->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDoesntHave(
                        'serviceBookings',
                        fn(Builder $q) =>
                        $q->where('available_end_date', '>=', now())
                    )
                ),
        ];
    }
}
