<?php

namespace App\Filament\Provider\Resources\PropertyResource\Pages;

use App\Filament\Provider\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyPeriod;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected array $availabilityDaysToSave = [];
    protected array $availability = [];

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
                'id' => $availabilityModel->id,
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

    protected function beforeSave()
    {
        $exists = Property::where('id', '!=', $this->record->id)
            ->where(function ($query) {
                $query->where('name_en', $this->data['name']['en'])
                    ->orWhere('name_ar', $this->data['name']['ar']);
            })->orWhere(function ($query) {
                $query->where('id', '!=', $this->record->id)
                    ->where(function ($q) {
                        $q->where('address->ar', $this->data['name']['ar'])
                            ->orWhere('address->en', $this->data['name']['en']);
                    });
            })->exists();

        if ($exists) {
            Notification::make()
                ->title(__('panel.host.property-update-error-title'))
                ->body(__('panel.host.property-update-error-body'))
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        $data = $this->data;
        $data['host_id'] = auth()->id();

        $availabilities = $this->data['availabilities'] ?? [];

        $this->availability = [];
        $this->availabilityDaysToSave = [];

        $availabilitiesList = [];

        foreach ($availabilities as $a) {
            $uuid = $a['id'] ?? ('temp_' . (string) Str::uuid());
            $a['uuid'] = $uuid;
            $availabilitiesList[] = $a;
        }

        $parent = array_shift($availabilitiesList);
        $parentStart = $parent['availability_start_date'];
        $parentEnd = $parent['availability_end_date'];

        $this->availability[$parent['uuid']] = [
            'type' => $parent['type'],
            'availability_start_date' => $parentStart,
            'availability_end_date' => $parentEnd,
        ];

        $childRanges = [];

        foreach ($availabilitiesList as $child) {
            $uuid = $child['uuid'];
            $childStart = $child['availability_start_date'];
            $childEnd = $child['availability_end_date'];

            if ($childStart < $parentStart || $childEnd > $parentEnd) {
                Notification::make()
                    ->title(__('panel.host.service-create-error-title'))
                    ->body(__('panel.host.service-out-of-range-error'))
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }

            foreach ($childRanges as [$start, $end]) {
                if ($childStart <= $end && $childEnd >= $start) {
                    Notification::make()
                        ->title(__('panel.host.service-create-error-title'))
                        ->body(__('panel.host.service-overlap-error'))
                        ->danger()
                        ->persistent()
                        ->send();

                    $this->halt();
                }
            }

            $childRanges[] = [$childStart, $childEnd];

            $this->availability[$uuid] = [
                'type' => $child['type'],
                'availability_start_date' => $childStart,
                'availability_end_date' => $childEnd,
                'parent_id' => 'PENDING',
            ];
        }

        $periods = $data['periods'] ?? [];

        $morning = collect($periods)->firstWhere('type', 1);
        $evening = collect($periods)->firstWhere('type', 2);

        if ($morning && $evening) {
            $mStart = strtotime($morning['start_time']);
            $mEnd = strtotime($morning['end_time']);
            $eStart = strtotime($evening['start_time']);
            $eEnd = strtotime($evening['end_time']);

            if ($mStart < $eEnd && $eStart < $mEnd) {
                throw ValidationException::withMessages([
                    'periods' => __('panel.host.morning-evening-overlap-error'),
                ]);
            }
        }

        foreach ($availabilities as $availability) {
            $uuid = $availability['id'] ?? ('temp_' . (string) Str::uuid());

            foreach ([1, 2, 3] as $periodType) {
                $key = "availabilityDays_{$periodType}";
                $entries = $availability[$key] ?? [];

                $grouped = [];

                foreach ($entries as $entry) {
                    if (empty($entry['day_of_week']) || $entry['price'] === null) continue;

                    $groupKey = $entry['property_period_id'] . '|' . $entry['price'];
                    $grouped[$groupKey]['property_period_id'] = $entry['property_period_id'];
                    $grouped[$groupKey]['price'] = $entry['price'];
                    $grouped[$groupKey]['day_of_week'] = array_merge(
                        $grouped[$groupKey]['day_of_week'] ?? [],
                        $entry['day_of_week']
                    );
                }

                foreach ($grouped as $group) {
                    $group['day_of_week'] = array_values(array_unique($group['day_of_week']));

                    $this->availabilityDaysToSave[] = [
                        'availability_id' => $uuid,
                        'type' => $periodType,
                        'price' => $group['price'],
                        'day_of_week' => $group['day_of_week'],
                    ];
                }
            }
        }

        unset($this->data['availabilities']);
        return $data;
    }

    protected function afterSave(): void
    {
        // 🧹 1. حذف كل availabilities والأيام المرتبطة بها
        $this->record->availabilities()->each(function ($availability) {
            $availability->availabilityDays()->delete();
            $availability->delete();
        });

        // 🧱 2. إعادة إنشاء availabilities
        $createdAvailabilityMap = [];
        $parentAvailabilityId = null;

        foreach ($this->availability as $id => $availabilityData) {
            if ($parentAvailabilityId) {
                $availabilityData['parent_id'] = $parentAvailabilityId;
            }

            $availability = $this->record->availabilities()->create($availabilityData);

            if (! $parentAvailabilityId) {
                $parentAvailabilityId = $availability->id;
            }

            $createdAvailabilityMap[$id] = $availability;
        }

        // 🧭 3. تحميل كل الفترات الزمنية (property_periods)
        $propertyPeriods = PropertyPeriod::where('property_id', $this->record->id)->get();

        // ⛓️ 4. إنشاء days كما هي (سطر بسطر)
        foreach ($this->availabilityDaysToSave as $day) {
            $availability = $createdAvailabilityMap[$day['availability_id']] ?? null;
            $period = $propertyPeriods->firstWhere('type', $day['type']);

            if (! $availability || ! $period) continue;

            foreach ($day['day_of_week'] as $dow) {
                $availability->availabilityDays()->create([
                    'property_period_id' => $period->id,
                    'day_of_week' => $dow,
                    'price' => $day['price'],
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
