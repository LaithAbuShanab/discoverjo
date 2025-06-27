<?php

namespace App\Rules;

use App\Models\PropertyReservation;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfDateExistsInPropertyAndAvailableEditRule implements ValidationRule ,DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            empty($this->data['period_type']) ||
            empty($this->data['check_in']) ||
            empty($this->data['check_out']) ||
            empty($this->data['reservation_id'])
        ) {
            $fail(__('Missing required data.'));
            return;
        }

        $reservation = PropertyReservation::with('property.periods', 'property.availabilities.availabilityDays.period')
            ->find($this->data['reservation_id']);

        if (! $reservation || ! $reservation->property) {
            $fail(__('Reservation or associated property not found.'));
            return;
        }

        $property = $reservation->property;

        $periodMap = [
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        ];

        $requestedType = $periodMap[$this->data['period_type']] ?? null;

        if (! $requestedType) {
            $fail(__('Invalid period type.'));
            return;
        }

        // Define conflicting types based on requested period
        $conflictingTypes = match ($requestedType) {
            1, 2 => [$requestedType, 3], // Morning/evening conflict with day
            3 => [1, 2, 3],              // Day conflicts with all
            default => [$requestedType],
        };

        $start = \Carbon\Carbon::parse($this->data['check_in'])->startOfDay();
        $end = \Carbon\Carbon::parse($this->data['check_out'])->startOfDay();

        $periodIds = $property->periods->whereIn('type', $conflictingTypes)->pluck('id');

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dayName = $cursor->format('l');

            // ✅ Check availability on this day
            $isAvailable = $property->availabilities->contains(function ($availability) use ($cursor, $conflictingTypes, $dayName) {
                return $cursor->between($availability->availability_start_date, $availability->availability_end_date)
                    && $availability->availabilityDays->contains(function ($day) use ($conflictingTypes, $dayName) {
                        return in_array($day->period->type, $conflictingTypes)
                            && $day->day_of_week === $dayName;
                    });
            });

            if (! $isAvailable) {
                $fail(__('Date :date is not available for the selected period.', ['date' => $cursor->toDateString()]));
                return;
            }

            // ✅ Check for conflicting reservation
            $isReserved = PropertyReservation::where('property_id', $property->id)
                ->where('status', '!=', 2) // Skip cancelled
                ->where('id', '!=', $reservation->id) // Exclude the current reservation
                ->whereIn('property_period_id', $periodIds)
                ->where('check_in', '<=', $cursor)
                ->where('check_out', '>=', $cursor)
                ->exists();

            if ($isReserved) {
                $fail(__('Date :date is already reserved for a conflicting period.', ['date' => $cursor->toDateString()]));
                return;
            }

            $cursor->addDay();
        }
    }



}
