<?php

namespace App\Filament\Resources\TopTenResource\Widgets;

use App\Models\Place;
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
//    protected function getStats(): array
//    {
//        // Get current date and find the most recent Thursday
//        $now = Carbon::now();
//        $endDate = $now->copy()->endOfDay();
//
//        // Get last Thursday (if today is Thursday, it returns today)
//        $startDate = $now->copy()->startOfWeek(Carbon::MONDAY)->subDays(4); // last Thursday
//
//
//        // If today is Thursday, we want the range to be from *last* Thursday to today
//        if ($now->isThursday()) {
//            $startDate = $now->copy()->subDays(($now->dayOfWeek + 3) % 7 + 7)->startOfDay();
//
//        }
//
//        // Get Top Ten Place IDs
//        $topTenPlacesId = TopTen::pluck('place_id');
//
//        // Base query conditions
//        $baseConditions = [
//            ['log_name', '=', 'place'],
//            ['subject_type', '=', 'App\Models\Place'],
//        ];
//
//        // Query activities for Top Ten places within the date range
//        $topTenQuery = Activity::where($baseConditions)
//            ->whereIn('subject_id', $topTenPlacesId)
//            ->whereBetween('created_at', [$startDate, $endDate]);
//
//        // Total views
//        $totalTopTenViews = (clone $topTenQuery)->count();
//
//        // Unique authenticated users by place
//        $uniqueAuthUsers = (clone $topTenQuery)
//            ->where('causer_type', 'App\Models\User')
//            ->whereNotNull('causer_id')
//            ->select('causer_id', 'subject_id')
//            ->groupBy('causer_id', 'subject_id')
//            ->get()
//            ->unique(fn ($item) => $item->causer_id . '-' . $item->subject_id)
//            ->count();
//
//        // Unique guest views (by IP and place)
//        $uniqueGuests = (clone $topTenQuery)
//            ->whereNull('causer_type')
//            ->whereRaw("JSON_EXTRACT(properties, '$.ip') IS NOT NULL")
//            ->select('subject_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(properties, '$.ip')) as ip"))
//            ->get()
//            ->unique(fn ($item) => $item->ip . '-' . $item->subject_id)
//            ->count();
//
//        return [
//            Stat::make('🧑‍🦱 Total Top Ten views', $totalTopTenViews)
//                ->description("Views from last Thursday to today")
//                ->descriptionIcon('heroicon-o-eye')
//                ->color('info'),
//
//            Stat::make('🧑‍🦱 Unique Auth Users', $uniqueAuthUsers)
//                ->description('Unique authenticated users by place')
//                ->descriptionIcon('heroicon-o-user')
//                ->color('success'),
//
//            Stat::make('🧑‍🦱 Unique Guest Users', $uniqueGuests)
//                ->description('Unique guests by IP and place')
//                ->descriptionIcon('heroicon-o-user')
//                ->color('warning'),
//        ];
//    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $endDate = $now->copy()->endOfDay();

        // Calculate last Thursday manually
        $startDate = $now->copy()->subDays(($now->dayOfWeek + 3) % 7 + 7)->startOfDay();

        // Get Top Ten Place IDs
        $topTenPlacesId = TopTen::pluck('place_id');

        // Base query conditions
        $baseConditions = [
            ['log_name', '=', 'place'],
            ['subject_type', '=', 'App\Models\Place'],
        ];

        // Global activity logs in date range for top ten
        $logs = Activity::where($baseConditions)
            ->whereIn('subject_id', $topTenPlacesId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by place_id
        $placeStats = $logs->groupBy('subject_id')->map(function ($items, $placeId) {
            $total = $items->count();

            $authUsers = $items->filter(fn ($item) =>
                $item->causer_type === 'App\Models\User' && $item->causer_id !== null
            )->unique(fn ($item) => $item->causer_id . '-' . $item->subject_id)->count();

            $guestUsers = $items->filter(fn ($item) =>
                $item->causer_type === null &&
                !empty(data_get($item->properties, 'ip'))
            )->unique(fn ($item) =>
                data_get($item->properties, 'ip') . '-' . $item->subject_id
            )->count();

            return [
                'place_id' => Place::find($placeId)?->name,
                'total' => $total,
                'auth' => $authUsers,
                'guest' => $guestUsers,
            ];
        });

        // Map to Filament Stats
        $stats = [];

        foreach ($placeStats->sortByDesc('total') as $stat) {
            $stats[] = Stat::make("📍 {$stat['place_id']}", $stat['total'])
                ->description("Auth: {$stat['auth']} | Guest: {$stat['guest']}")
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info');
        }

        return $stats;
    }

}

