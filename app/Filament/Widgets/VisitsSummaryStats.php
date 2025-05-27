<?php

namespace App\Filament\Widgets;

use App\Models\TopTen;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

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
        $topTenPlacesId = TopTen::get()->pluck('place_id');
        $topTenQuery = Activity::where('log_name','place')->where('subject_type','App\Models\Place')->whereIn('subject_id', $topTenPlacesId);
        $authUserQuery = Activity::select('causer_id', 'subject_id', DB::raw('NULL as ip'),'created_at')
            ->where('log_name', 'place')
            ->where('subject_type', 'App\Models\Place')
            ->where('causer_type', 'App\Models\User')
            ->whereIn('subject_id', $topTenPlacesId)
            ->distinct();


// Group B: Guests (causer_type is null, distinct IPs)
        $guestQuery = Activity::select(DB::raw('NULL as causer_id'), 'subject_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(properties, '$.ip')) as ip"),'')
            ->where('log_name', 'place')
            ->where('subject_type', 'App\Models\Place')
            ->whereNull('causer_type')
            ->whereIn('subject_id', $topTenPlacesId)
            ->whereRaw("JSON_EXTRACT(properties, '$.ip') IS NOT NULL")
            ->distinct();



        // Apply date filter only if both dates are set
        if ($startDate && $endDate) {
            $visitQuery->whereBetween('created_at', [$startDate, $endDate]);
            $userQuery->whereBetween('created_at', [$startDate, $endDate]);
            $guestQuery->whereBetween('created_at', [$startDate, $endDate]);
            $topTenQuery->whereBetween('created_at', [$startDate, $endDate]);
            $authUserQuery->whereBetween('created_at', [$startDate, $endDate]);
            $guestQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Get counts
        $allVisits   = $visitQuery->count();
        $userVisits  = $userQuery->count();
        $guestVisits = $guestQuery->count();
        $topTenUnique= $authUserQuery->count();
        $topTen = $topTenQuery->count();
        $guest = $guestQuery->count();

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

            Stat::make('🧑‍🦱 Total Top Ten views', $topTen)
                ->description('Total number of views across the top 10 places, including both authenticated users and guests. Repeated views are counted.')
                ->descriptionIcon('heroicon-o-eye')
                ->chart([5, 10, 8, 12, 15, 12, 16])
                ->color('info'),

            Stat::make('🧑‍🦱 Top Ten views unique Auth user ', $topTenUnique)
                ->description('Number of distinct authenticated users who viewed the top 10 places. Each user is counted only once per place.')
                ->descriptionIcon('heroicon-o-eye')
                ->chart([5, 10, 8, 12, 15, 12, 16])
                ->color('danger'),

            Stat::make('🧑‍🦱 Top Ten views unique Guest ', $guest)
                ->description('Number of distinct guest views for the top 10 places, identified by unique IP addresses. Each IP is counted once per place.')
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

