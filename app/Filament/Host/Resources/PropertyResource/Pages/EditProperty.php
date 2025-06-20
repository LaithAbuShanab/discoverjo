<?php

namespace App\Filament\Host\Resources\PropertyResource\Pages;

use App\Filament\Host\Resources\PropertyResource;
use App\Models\PropertyPeriod;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected array $availabilityDaysToSave = [];
    protected array $availability = [];
    protected array $availabilitiesData = [];
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function afterFill(): void
    {
        $availabilities = $this->record->availabilities()->with('availabilityDays')->get();
        $periods = PropertyPeriod::where('property_id', $this->record->id)->get();

        $this->data['availabilities'] = []; // 🔑 Overwrite any auto-filled records

        foreach ($availabilities as $index => $availabilityModel) {
            $availabilityData = [
                'type' => $availabilityModel->type,
                'availability_start_date' => $availabilityModel->availability_start_date,
                'availability_end_date' => $availabilityModel->availability_end_date,
            ];

            foreach ($periods as $period) {
                $periodId = $period->id;
                $periodType = $period->type;

                $matchingDays = $availabilityModel->availabilityDays
                    ->where('property_period_id', $periodId)
                    ->groupBy(fn($item) => $item->price);

                $formatted = [];

                foreach ($matchingDays as $price => $grouped) {
                    $formatted[] = [
                        'property_period_id' => $periodId,
                        'price' => $price,
                        'day_of_week' => $grouped->pluck('day_of_week')->unique()->values()->toArray(),
                    ];
                }

                if (!empty($formatted)) {
                    $availabilityData["availabilityDays_{$periodType}"] = $formatted;
                }
            }

            $this->data['availabilities'][] = $availabilityData;
        }
    }


}
