<?php

namespace App\Filament\Resources\PlaceResource\Widgets;

use Filament\Forms\Components\DatePicker;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Place;

class MostViewedPlacesChart extends ChartWidget
{
    protected static ?string $heading = 'Most Viewed Places';

    public ?string $startDate = null;
    public ?string $endDate = null;
    protected int | string | array $columnSpan = [ 'md' => 2, 'xl' => 3, ];

    protected function getData(): array
    {
        $baseConditions = [
            ['log_name', '=', 'place'],
            ['subject_type', '=', 'App\Models\Place'],
        ];
        $data = DB::table('activity_log')
            ->where($baseConditions)
            ->select('subject_id', DB::raw('count(*) as views'))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->groupBy('subject_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $labels = [];
        $views = [];

        foreach ($data as $item) {
            $placeName = Place::find($item->subject_id)?->name ?? 'Unknown';
            $labels[] = $placeName;
            $views[] = $item->views;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $views,
                ],
            ],
            'labels' => $labels,
        ];
    }
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Start Date')
                ->default(now()->startOfMonth()),
            DatePicker::make('endDate')
                ->label('End Date')
                ->default(now()),
        ];
    }

    protected function filterForm(): array
    {
        return $this->getFormSchema();
    }
    protected function getType(): string
    {
        return 'bar'; // or 'line', 'pie', etc.
    }
}
