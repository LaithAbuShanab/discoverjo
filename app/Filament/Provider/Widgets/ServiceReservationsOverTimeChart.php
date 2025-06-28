<?php

namespace App\Filament\Provider\Widgets;

use App\Models\{Service, ServiceReservation, User};
use Carbon\Carbon;
use Filament\Widgets\{ChartWidget, Concerns\InteractsWithPageFilters};
use Illuminate\Support\Facades\Auth;

class ServiceReservationsOverTimeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        $user = User::find(Auth::guard('provider')->id());
        if ($user->userTypes()->where('type', 3)->exists()) {
            return true;
        }
        return false;
    }

    public function getHeading(): ?string
    {
        return __('panel.provider.reservations_over_time');
    }

    protected static ?int $sort = 3;
    protected static string $color = 'primary';

    protected function getData(): array
    {
        $startDateInput = $this->filters['startDate'] ?? null;
        $endDateInput = $this->filters['endDate'] ?? null;

        $startDate = !empty($startDateInput)
            ? Carbon::parse($startDateInput)
            : now()->startOfDay();

        $endDate = !empty($endDateInput)
            ? Carbon::parse($endDateInput)
            : now()->addDays(6)->endOfDay();

        $serviceIds = Service::query()
            ->where('provider_type', 'App\\Models\\User')
            ->where('provider_id', Auth::guard('provider')->id())
            ->pluck('id')
            ->toArray();

        $reservations = ServiceReservation::query()
            ->whereIn('service_id', $serviceIds)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->get()
            ->groupBy(fn($reservation) => Carbon::parse($reservation->date)->format('Y-m-d'));

        $labels = [];
        $data = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        Carbon::setLocale(app()->getLocale());

        foreach ($period as $date) {
            $formatted = app()->getLocale() === 'ar'
                ? $date->translatedFormat('j F')
                : $date->translatedFormat('d M');

            $labels[] = $formatted;
            $data[] = $reservations->get($date->format('Y-m-d'), collect())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('panel.provider.reservations'),
                    'data' => $data,
                    'fill' => false,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 1,
                        'beginAtZero' => true,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
