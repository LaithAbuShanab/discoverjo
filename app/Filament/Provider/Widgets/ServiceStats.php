<?php

namespace App\Filament\Provider\Widgets;

use App\Models\Service;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = !is_null($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])
            : null;

        $endDate = !is_null($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])
            : now();

        $query = Service::query()
            ->where('provider_type', 'App\\Models\\User')
            ->where('provider_id', auth()->id());

        if ($startDate) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        $count = $query->count();

        return [
            Stat::make('Services Created', $count)
                ->description('Services created in selected period')
                ->descriptionIcon('heroicon-o-briefcase')
                ->chart([20, 25, 18, 22, 30, 24, 28])
                ->color('success'),
        ];
    }
}
