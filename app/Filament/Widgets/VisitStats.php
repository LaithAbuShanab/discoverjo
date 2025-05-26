<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Visit;
use Carbon\Carbon;

class VisitStats extends ChartWidget
{
    protected static ?string $heading = 'Daily Visits Breakdown 📈';
    protected static ?int $sort = 2;

    /**
     * Number of days to show
     */
    protected int $daysBack = 7;

    /**
     * Get the chart type
     */    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): ?array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                ],
            ],
        ];
    }

    /**
     * Prepare data for chart
     */
    protected function getData(): array
    {
        // Generate list of days
        $days = collect(range(0, $this->daysBack - 1))
            ->map(fn($i) => Carbon::now()->subDays($i)->startOfDay())
            ->reverse()
            ->values();

        // Initialize data structure
        $registeredData = [];
        $guestData = [];
        $labels = [];

        // Fetch visits once for all days
        $visits = Visit::query()
            ->whereDate('created_at', '>=', $days->first()->toDateString())
            ->whereDate('created_at', '<=', $days->last()->toDateString())
            ->select('created_at', 'user_id')
            ->get()
            ->groupBy(fn($visit) => Carbon::parse($visit->created_at)->format('Y-m-d'));

        foreach ($days as $day) {
            $dateKey = $day->format('Y-m-d');
            $labels[] = $day->format('D d'); // e.g. "Mon 20"

            $dayVisits = $visits[$dateKey] ?? collect();

            $registeredData[] = $dayVisits->whereNotNull('user_id')->count();
            $guestData[] = $dayVisits->whereNull('user_id')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => '👤 Registered Users',
                    'data' => $registeredData,
                    'fill' => false,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.3,
                ],
                [
                    'label' => '🧑‍🦱 Guests',
                    'data' => $guestData,
                    'fill' => false,
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
