<?php

namespace App\Filament\Provider\Widgets;

use App\Models\{Property, PropertyReservation, User};
use Carbon\Carbon;
use Filament\Widgets\{Concerns\InteractsWithPageFilters, StatsOverviewWidget as BaseWidget, StatsOverviewWidget\Stat};
use Illuminate\Support\Facades\Auth;

class HostRevenueAndCancellationStats extends BaseWidget
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        $user = User::find(Auth::guard('provider')->id());
        if ($user->userTypes()->where('type', 4)->exists()) {
            return true;
        }
        return false;
    }

    protected function getHeading(): ?string
    {
        return __('panel.host.host-revenue-and-cancellation');
    }

    protected static ?int $sort = 2;

    /**
     * Generate statistics for total revenue, cancellation loss, and cancellation rate for host reservations.
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

        // Get property IDs for the current host
        $propertyIds = Property::query()
            ->where('host_id', Auth::guard('provider')->id())
            ->pluck('id')
            ->toArray();

        // Base query for the host's property reservations
        $query = PropertyReservation::query()
            ->whereIn('property_id', $propertyIds);

        // Filter by date range if provided
        if ($startDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        // Clone to calculate
        $allReservations = (clone $query)->get();

        $totalRevenue = $allReservations->sum('total_price');
        $cancelledReservations = $allReservations->where('status', 2);
        $cancelledRevenueLoss = $cancelledReservations->sum('total_price');

        $totalCount = $allReservations->count();
        $cancelledCount = $cancelledReservations->count();
        $rate = $totalCount > 0 ? round(($cancelledCount / $totalCount) * 100, 2) : 0;

        return [
            Stat::make('Total Revenue', number_format($totalRevenue, 2) . ' ' . __('panel.host.jor'))
                ->label(__('panel.host.total-revenue'))
                ->description(__('panel.host.total-revenue-description'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Loss from Cancellations', number_format($cancelledRevenueLoss, 2) . ' ' . __('panel.host.jor'))
                ->label(__('panel.host.loss-from-cancellations'))
                ->description(__('panel.host.loss-from-description'))
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('danger'),

            Stat::make('Cancellation Rate', "{$rate}%")
                ->label(__('panel.host.cancellation-rate'))
                ->description(__('panel.host.cancellation-rate-description'))
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($rate < 25 ? 'success' : ($rate < 50 ? 'warning' : 'danger')),
        ];
    }
}
