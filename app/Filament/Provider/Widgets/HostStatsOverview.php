<?php

namespace App\Filament\Provider\Widgets;

use App\Models\{Property, PropertyReservation, User};
use Carbon\Carbon;
use Filament\Widgets\{Concerns\InteractsWithPageFilters, StatsOverviewWidget as BaseWidget, StatsOverviewWidget\Stat};
use Illuminate\Support\Facades\Auth;

class HostStatsOverview extends BaseWidget
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
        return __('panel.host.host-status-stats');
    }

    protected static ?int $sort = 1;


    /**
     * Return statistics cards for the host dashboard.
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

        // Get all properties owned by the host
        $propertyIds = Property::query()
            ->where('host_id', Auth::guard('provider')->id())
            ->pluck('id');

        // Base reservation query filtered by property ownership
        $query = PropertyReservation::query()
            ->whereIn('property_id', $propertyIds);

        if ($startDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        // Status counts
        $pendingCount = (clone $query)->where('status', 0)->count();
        $confirmedCount = (clone $query)->where('status', 1)->count();
        $cancelledCount = (clone $query)->where('status', 2)->count();
        $completedCount = (clone $query)->where('status', 3)->count();

        return [
            Stat::make('Pending', $pendingCount)
                ->label(__('panel.host.pending'))
                ->description(__('panel.host.pending-reservations'))
                ->descriptionIcon('heroicon-o-clock')
                ->chart([2, 4, 3, 5, 6, 4, 5])
                ->color('warning'),

            Stat::make('Confirmed', $confirmedCount)
                ->label(__('panel.host.confirmed'))
                ->description(__('panel.host.confirmed-reservations'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart([5, 6, 7, 6, 8, 7, 9])
                ->color('info'),

            Stat::make('Cancelled', $cancelledCount)
                ->label(__('panel.host.cancelled'))
                ->description(__('panel.host.cancelled-reservations'))
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart([1, 2, 1, 3, 2, 2, 1])
                ->color('danger'),

            Stat::make('Completed', $completedCount)
                ->label(__('panel.host.completed'))
                ->description(__('panel.host.completed-reservations'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->chart([3, 5, 6, 7, 8, 8, 9])
                ->color('success'),
        ];
    }
}
