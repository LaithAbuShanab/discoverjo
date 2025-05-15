<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return false;
    }

    /**
     * Get the stats to display in the widget.
     *
     * @return array
     */
    protected function getStats(): array
    {
        $startDate = !is_null($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])
            : null;

        $endDate = !is_null($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])
            : now();

        // Query for total users without soft delete check
        $usersQuery = DB::table('users')
            ->selectRaw('COUNT(id) as new_users');

        if ($startDate) {
            $usersQuery->whereBetween('created_at', [
                $startDate->startOfDay()->toDateTimeString(),
                $endDate->endOfDay()->toDateTimeString(),
            ]);
        }

        $usersData = $usersQuery->first();

        $newUsers = $usersData->new_users ?? 0;

        // Format numbers for better display
        $formatNumber = function (int|float $number): string {
            if ($number < 1000) {
                return number_format($number, 0);
            }

            if ($number < 1000000) {
                return number_format($number / 1000, 2) . 'k';
            }

            return number_format($number / 1000000, 2) . 'm';
        };

        return [
            Stat::make('New User Registrations', $formatNumber($newUsers))
                ->description('Users registered in the selected period')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('success'),
        ];
    }
}
