<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Property;
use App\Models\PropertyReservation;
use Carbon\Carbon;


class CheckIfDateExistsInPropertyAndAvailableRule implements ValidationRule, DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            empty($this->data['property_slug']) ||
            empty($this->data['period_type']) ||
            empty($this->data['check_in']) ||
            empty($this->data['check_out'])
        ) {
            $fail(__('validation.api.missing_parameters'));
            return;
        }

        if($this->data['period_type'] !== 'morning') {
            $this->data['check_out'] = date('Y-m-d', strtotime($this->data['check_out'] . ' -1 day'));
        }

        $property = Property::where('slug', $this->data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->first();

        if (!$property) {
            $fail(__('validation.api.property_not_found'));
            return;
        }

        $periodMap = [
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        ];

        $requestedType = $periodMap[$this->data['period_type']] ?? null;

        if (!$requestedType) {
            $fail(__('validation.api.invalid_period_type'));
            return;
        }

        // If booking day => conflict with morning & evening
        // If booking morning/evening => also conflict with full day
        $conflictingTypes = match ($requestedType) {
            1, 2 => [$requestedType, 3],
            3    => [1, 2, 3],
            default => [$requestedType],
        };

        $start = Carbon::parse($this->data['check_in'])->startOfDay();
        $end = Carbon::parse($this->data['check_out'])->startOfDay();

        $periodIds = $property->periods
            ->whereIn('type', $conflictingTypes)
            ->pluck('id');

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dayName = $cursor->format('l');

            // ✅ Check availability
            $isAvailable = $property->availabilities->contains(function ($availability) use ($cursor, $conflictingTypes, $dayName) {
                return $cursor->between($availability->availability_start_date, $availability->availability_end_date)
                    && $availability->availabilityDays->contains(function ($day) use ($conflictingTypes, $dayName) {
                        return in_array($day->period->type, $conflictingTypes)
                            && $day->day_of_week === $dayName;
                    });
            });

            if (!$isAvailable) {
                $fail(__('validation.api.date_not_available', ['date' => $cursor->toDateString()]));
                return;
            }

            // ✅ Check conflicting reservations (excluding cancelled)
            $isReserved = PropertyReservation::where('property_id', $property->id)
                ->where('status', '!=', 2) // Skip cancelled
                ->whereIn('property_period_id', $periodIds)
                ->where('check_in', '<=', $cursor)
                ->where('check_out', '>=', $cursor)
                ->exists();

            if ($isReserved) {
                $fail(__('validation.api.date_conflict_reserved', ['date' => $cursor->toDateString()]));
                return;
            }

            $cursor->addDay();
        }
    }
}
