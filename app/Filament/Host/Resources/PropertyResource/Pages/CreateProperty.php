<?php

namespace App\Filament\Host\Resources\PropertyResource\Pages;

use App\Filament\Host\Resources\PropertyResource;
use App\Models\Property;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateProperty extends CreateRecord
{
    protected static string $resource = PropertyResource::class;
    protected array $availabilityDaysToSave = [];
    protected array $availability = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $periods = $data['periods'] ?? [];
        $morning = collect($periods)->firstWhere('type', 1);
        $evening = collect($periods)->firstWhere('type', 2);
        $overnight = collect($periods)->firstWhere('type', 3);

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

        $data['host_id'] = auth()->id();

        $availabilities = $this->data['availabilities'] ?? [];
        $availabilitiesList = [];

        foreach ($availabilities as $uuid => $availability) {
            $availability['uuid'] = $uuid;
            $availabilitiesList[] = $availability;
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

            // ✅ Check for overlap with previously stored children
            foreach ($childRanges as [$start, $end, $conflictUuid]) {
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

            $childRanges[] = [$childStart, $childEnd, $uuid];

            $this->availability[$uuid] = [
                'type' => $child['type'],
                'availability_start_date' => $childStart,
                'availability_end_date' => $childEnd,
                'parent_id' => 'PENDING',
            ];
        }

        // Process all availability days
        foreach ($availabilities as $uuid => $availability) {
            foreach ([1, 2, 3] as $periodType) {
                $key = "availabilityDays_{$periodType}";

                if (! isset($availability[$key])) continue;

                foreach ($availability[$key] as $entry) {
                    if (empty($entry['day_of_week']) || $entry['price'] === null) continue;

                    foreach ($entry['day_of_week'] as $day) {

                        $this->availabilityDaysToSave[] = [
                            'type' => $entry['property_period_id'],
                            'availability_uuid' => $uuid,
                            'day_of_week' => $day,
                            'price' => $entry['price'],
                        ];
                    }
                }
            }
        }

        unset($this->data['availabilities']);

        return $data;
    }

    protected function beforeCreate(): void
    {

        $exists = Property::where(function ($query) {
            $query->where('name_en', $this->data['name']['en'])
                ->orWhere('name_ar', $this->data['name']['ar']);
        })->orWhere(function ($query) {
            $query->where('address->ar', $this->data['name']['ar'])
                ->orWhere('address->en', $this->data['name']['en']);
        })->exists();

        if ($exists) {
            Notification::make()
                ->title(__('panel.host.property-exists-title'))
                ->body(__('panel.host.property-exists-body'))
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        $parentAvailabilityId = null;

        foreach ($this->availability as $uuid => $availabilityData) {
            // Set parent_id if it's not the first one
            if ($parentAvailabilityId) {
                $availabilityData['parent_id'] = $parentAvailabilityId;
            }
            // Create the availability
            $availability = $this->record->availabilities()->create($availabilityData);

            // Store the first created ID as the parent
            if (! $parentAvailabilityId) {
                $parentAvailabilityId = $availability->id;
            }

            // Create related availability days
            foreach ($this->availabilityDaysToSave as $availabilityDay) {
                if ($availabilityDay['availability_uuid'] === $uuid) {
                    $period = $this->record->periods->where('type', $availabilityDay['type'])->first();
                    unset($availabilityDay['availability_uuid']);
                    unset($availabilityDay['type']);
                    $availabilityDay['property_period_id'] = $period?->id;
                    $availability->availabilityDays()->create($availabilityDay);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
