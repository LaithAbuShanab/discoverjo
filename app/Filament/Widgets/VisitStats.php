<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Visit;
use Carbon\Carbon;

class VisitStats extends ChartWidget
{
    protected static ?string $heading = 'Monthly Visits 📊';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Generate the last 12 months as Carbon objects
        $months = collect(range(0, 11))
            ->map(fn($i) => Carbon::now()->subMonths($i)->startOfMonth())
            ->reverse()
            ->values();

        // Get monthly counts
        $data = $months->map(function ($month) {
            return Visit::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        });

        // Month names as labels
        $labels = $months->map(fn($month) => $month->format('F')); // e.g. "January"

        return [
            'datasets' => [
                [
                    'label' => 'Visits Count',
                    'data' => $data->toArray(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // or 'line' if you prefer a line chart
    }
}
