<?php

namespace App\Filament\Provider\Widgets;

use App\Models\Service;
use App\Models\ServiceReservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingStatusStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    /**
     * Define stats overview cards.
     *
     * @return Stat[]
     */
    protected function getStats(): array
    {
        $startDate = !is_null($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])
            : null;

        $endDate = !is_null($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])
            : now();

        $serviceIds = Service::query()
            ->where('provider_type', 'App\\Models\\User')
            ->where('provider_id', Auth::guard('provider')->user()->id)
            ->pluck('id');

        $query = ServiceReservation::query()
            ->whereIn('service_id', $serviceIds);

        if ($startDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        $pendingCount = (clone $query)->where('status', 0)->count();
        $confirmedCount = (clone $query)->where('status', 1)->count();
        $cancelledCount = (clone $query)->where('status', 2)->count();
        $completedCount = (clone $query)->where('status', 4)->count();

        return [
            Stat::make('Pending', $pendingCount)
                ->label(__('panel.provider.pending'))
                ->description(__('panel.provider.pending-reservations'))
                ->descriptionIcon('heroicon-o-clock')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('warning'),

            Stat::make('Confirmed', $confirmedCount)
                ->label(__('panel.provider.confirmed'))
                ->description(__('panel.provider.confirmed-reservations'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('info'),

            Stat::make('Cancelled', $cancelledCount)
                ->label(__('panel.provider.cancelled'))
                ->description(__('panel.provider.cancelled-reservations'))
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('danger'),

            Stat::make('Completed', $completedCount)
                ->label(__('panel.provider.completed'))
                ->description(__('panel.provider.completed-reservations'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('success'),
        ];
    }
}
