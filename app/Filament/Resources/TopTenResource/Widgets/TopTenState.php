<?php

namespace App\Filament\Resources\TopTenResource\Widgets;

use Filament\Forms\Components\DatePicker;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Spatie\Activitylog\Models\Activity;
use App\Models\TopTen;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;


class TopTenState extends BaseWidget
{
    use InteractsWithPageFilters;

    /**
     * Get statistics data for top ten places
     *
     * @return array<int, \Filament\Widgets\StatsOverviewWidget\Stat>
     */
    protected function getStats(): array
    {
        // Retrieve filter values
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Parse dates if present
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Get Top Ten Place IDs
        $topTenPlacesId = TopTen::pluck('place_id');

        // Base query conditions
        $baseConditions = [
            ['log_name', '=', 'place'],
            ['subject_type', '=', 'App\Models\Place'],
        ];

        // Global query scope for all activity related to Top Ten places
        $topTenQuery = Activity::where($baseConditions)
            ->whereIn('subject_id', $topTenPlacesId);

        // Apply date filters
        if ($startDate && $endDate) {
            $topTenQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Total views
        $totalTopTenViews = (clone $topTenQuery)->count();

        // Unique views by authenticated users
        $uniqueAuthUsers = (clone $topTenQuery)
            ->where('causer_type', 'App\Models\User')
            ->whereNotNull('causer_id')
            ->select('causer_id', 'subject_id')
            ->groupBy('causer_id', 'subject_id')
            ->get()
            ->unique(fn ($item) => $item->causer_id . '-' . $item->subject_id)
            ->count();

        // Unique views by guests (based on IP)
        $uniqueGuests = (clone $topTenQuery)
            ->whereNull('causer_type')
            ->whereRaw("JSON_EXTRACT(properties, '$.ip') IS NOT NULL")
            ->select('subject_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(properties, '$.ip')) as ip"))
            ->get()
            ->unique(fn ($item) => $item->ip . '-' . $item->subject_id)
            ->count();

        return [
            Stat::make('🧑‍🦱 Total Top Ten views', $totalTopTenViews)
                ->description('Total views (repeated counts)')
                ->descriptionIcon('heroicon-o-eye')
                ->color('info'),

            Stat::make('🧑‍🦱 Unique Auth Users', $uniqueAuthUsers)
                ->description('Unique authenticated users by place')
                ->descriptionIcon('heroicon-o-user')
                ->color('success'),

            Stat::make('🧑‍🦱 Unique Guest Users', $uniqueGuests)
                ->description('Unique guests by IP and place')
                ->descriptionIcon('heroicon-o-user')
                ->color('warning'),
        ];
    }
}

