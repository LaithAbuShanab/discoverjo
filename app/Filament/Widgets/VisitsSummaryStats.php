<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Visit;

class VisitsSummaryStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = !empty($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])->startOfDay()
            : null;

        $endDate = !empty($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])->endOfDay()
            : null;

        // Base queries
        $visitQuery = Visit::query();
        $userQuery = Visit::whereNotNull('user_id');
        $guestQuery = Visit::whereNull('user_id');

        // Apply date filter only if both dates are set
        if ($startDate && $endDate) {
            $visitQuery->whereBetween('created_at', [$startDate, $endDate]);
            $userQuery->whereBetween('created_at', [$startDate, $endDate]);
            $guestQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Get counts
        $allVisits   = $visitQuery->count();
        $userVisits  = $userQuery->count();
        $guestVisits = $guestQuery->count();

        return [
            Stat::make('📊 Total Visits', $allVisits)
                ->description($startDate ? 'In selected period' : 'All time')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('info'),

            Stat::make('👤 Registered User Visits', $userVisits)
                ->description('Users with accounts')
                ->descriptionIcon('heroicon-o-user')
                ->chart([10, 15, 12, 16, 20, 18, 22])
                ->color('danger'),

            Stat::make('🧑‍🦱 Guest Visits', $guestVisits)
                ->description('Based on IP address')
                ->descriptionIcon('heroicon-o-eye')
                ->chart([5, 10, 8, 12, 15, 12, 16])
                ->color('warning'),
        ];
    }

    protected function getFilters(): array
    {
        return [
            'startDate' => null,
            'endDate' => null,
        ];
    }
}

