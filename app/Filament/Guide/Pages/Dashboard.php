<?php

namespace App\Filament\Guide\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Str;


class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('panel.provider.start-date'))
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->label(__('panel.provider.end-date'))
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetFilters')
                ->label(__('panel.guide.reset-filters'))
                ->color('danger')
                ->outlined()
                ->action(fn() => $this->resetFilters()),
        ];
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'startDate' => null,
            'endDate' => null,
        ];

        foreach (session()->all() as $key => $value) {
            if (Str::endsWith($key, '_filters')) {
                session()->forget($key);
            }
        }

        $this->dispatch('$refresh');
    }
}
