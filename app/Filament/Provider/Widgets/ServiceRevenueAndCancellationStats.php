<?php

namespace App\Filament\Provider\Widgets;

use App\Models\{Service, ServiceReservation, User};
use Carbon\Carbon;
use Filament\Widgets\{Concerns\InteractsWithPageFilters, StatsOverviewWidget as BaseWidget, StatsOverviewWidget\Stat};
use Illuminate\Support\Facades\Auth;

class ServiceRevenueAndCancellationStats extends BaseWidget
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        $user = User::find(Auth::guard('provider')->id());
        if ($user->userTypes()->where('type', 3)->exists()) {
            return true;
        }
        return false;
    }

    protected function getHeading(): ?string
    {
        return __('panel.provider.service-revenue-and-cancellation');
    }

    protected static ?int $sort = 2;

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
            ->where('provider_id', Auth::guard('provider')->id())
            ->pluck('id')
            ->toArray();

        $query = ServiceReservation::query()
            ->whereIn('service_id', $serviceIds);

        if ($startDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        $allReservations = (clone $query)->get();

        // Revenue
        $totalRevenue = $allReservations->sum('total_price');

        // Cancellations
        $cancelledReservations = $allReservations->where('status', 2);
        $cancelledRevenueLoss = $cancelledReservations->sum('total_price');

        // Cancellation rate
        $total = $allReservations->count();
        $cancelledCount = $cancelledReservations->count();
        $rate = $total > 0 ? round(($cancelledCount / $total) * 100, 2) : 0;

        return [
            Stat::make('Total Revenue', number_format($totalRevenue, 2) . ' ' . __('panel.provider.jor'))
                ->label(__('panel.provider.total-revenue'))
                ->description(__('panel.provider.total-revenue-description'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Loss from Cancellations', number_format($cancelledRevenueLoss, 2) . ' ' . __('panel.provider.jor'))
                ->label(__('panel.provider.loss-from-cancellations'))
                ->description(__('panel.provider.loss-from-description'))
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('danger'),

            Stat::make('Cancellation Rate', "{$rate}%")
                ->label(__('panel.provider.cancellation-rate'))
                ->description(__('panel.provider.cancellation-rate-description'))
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($rate < 25 ? 'success' : ($rate < 50 ? 'warning' : 'danger')),
        ];
    }
}
