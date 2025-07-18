<?php

namespace App\Rules;

use App\Models\Property;
use App\Models\PropertyReservation;
use App\Models\PropertyReservationDetail;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfDateExistsInPropertyAndAvailableEditRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (
            empty($this->data['property_slug']) ||
            empty($this->data['check_in']) ||
            empty($this->data['check_out'])
        ) {
            $fail(__('validation.api.missing_parameters'));
            return;
        }

        //find the property by slug
        $propertyReservation = PropertyReservation::find($this->data['reservation_id']);
        $property = $propertyReservation->property;

        if (!$property) {
            $fail(__('validation.api.property_not_found'));
            return;
        }

        //retrieve the check in & check out
        $checkIn = Carbon::parse($this->data['check_in']);
        $checkOut = Carbon::parse($this->data['check_out']);

        // Validate the check in and check out the time match the period of property
        if (!$this->validateBookingTimes($checkIn, $checkOut, $property)) {
            $fail(__('validation.api.time_range_does_not_match_any_period'));
            return;
        }

        $onePeriod = $this->checkIfHasOnePeriod($property, $checkIn, $checkOut, $fail);

        if($onePeriod) {
            $bookingPattern =$onePeriod;
        }else{
            // Validate availability and reservations for the booking pattern will give date period type period id
            $bookingPattern = $this->analyzeBookingPattern($checkIn, $checkOut, $property);
        }

        foreach ($bookingPattern as $item) {
            $date = $item['date'];
            $periodId = $item['period_id'];
            $dayOfWeek = Carbon::parse($date)->format('l');

            // Check availability
            $isAvailable = $property->availabilities->contains(function ($availability) use ($date, $dayOfWeek, $periodId) {
                $cursor = Carbon::parse($date);
                return $cursor->between($availability->availability_start_date, $availability->availability_end_date)
                    && $availability->availabilityDays->contains(function ($day) use ($dayOfWeek, $periodId) {
                        return $day->day_of_week === $dayOfWeek && $day->property_period_id == $periodId;
                    });
            });

            if (!$isAvailable) {
                $fail(__('validation.api.date_not_available', ['date' => $date]));
                return;
            }

            // Check for conflicts with existing reservations
            $period = $property->periods->find($periodId);
            if ($period) {
                $fromDateTime = Carbon::parse($date . ' ' . $period->start_time);
                $toDateTime = Carbon::parse($date . ' ' . $period->end_time);

                $isReserved = PropertyReservationDetail::whereHas('reservation', function ($q) use ($property) {
                    $q->where('property_id', $property->id)
                        ->where('status', '!=', 2); // exclude cancelled
                })
                    ->where('property_period_id', $periodId)
                    ->where(function ($query) use ($fromDateTime, $toDateTime) {
                        $query->whereBetween('from_datetime', [$fromDateTime, $toDateTime])
                            ->orWhereBetween('to_datetime', [$fromDateTime, $toDateTime])
                            ->orWhere(function ($q) use ($fromDateTime, $toDateTime) {
                                $q->where('from_datetime', '<=', $fromDateTime)
                                    ->where('to_datetime', '>=', $toDateTime);
                            });
                    })
                    ->where('property_reservation_id', '!=', $this->data['reservation_id'])
                    ->exists();

                if ($isReserved) {
                    $fail(__('validation.api.date_conflict_reserved', ['date' => $date]));
                    return;
                }
            }
        }
    }

    private function validateBookingTimes(Carbon $checkIn, Carbon $checkOut, $property): bool
    {
        //retrieve the time of check in and check out
        $checkInTime = $checkIn->format('H:i:s');
        $checkOutTime = $checkOut->format('H:i:s');

        // Check if check-in time matches any period start time
        $validCheckIn = $property->periods->contains(function ($period) use ($checkInTime) {
            return $checkInTime === $period->start_time;
        });

        // Check if check-out time matches any period end time
        $validCheckOut = $property->periods->contains(function ($period) use ($checkOutTime) {
            return $checkOutTime === $period->end_time;
        });

        // will return boolean true if the time match
        return $validCheckIn && $validCheckOut;
    }

    // Include the same analyzeBookingPattern method here for validation
    public function analyzeBookingPattern(Carbon $checkIn, Carbon $checkOut, $property)
    {
        //first we will find he types of the property
        $periods = $property->periods->keyBy('type');
        $dayPeriod = $periods->get(3);     // full day
        $morningPeriod = $periods->get(1); // morning
        $eveningPeriod = $periods->get(2); // evening

        //retrieve the time of check in and checkout
        $checkInTime = $checkIn->format('H:i:s');
        $checkOutTime = $checkOut->format('H:i:s');

        $bookingItems = [];

        // ✅ 1. Check for full day (09:00 → next day 09:00) just one day 24 hours

        if (
            $dayPeriod &&
            $checkIn->copy()->addDay()->toDateTimeString() === $checkOut->toDateTimeString() &&
            $checkInTime === $dayPeriod->start_time &&
            $checkOutTime === $dayPeriod->end_time
        ) {
            return [[
                'date' => $checkIn->toDateString(),
                'period_type' => 'day',
                'period_id' => $dayPeriod->id,
            ]];
        }

        //just if the evening period
        $eveningDurationInHours =0;
        if ($eveningPeriod) {
            $baseDate = Carbon::today();

            $start = Carbon::parse($baseDate->format('Y-m-d') . ' ' . $eveningPeriod->start_time);
            $end = Carbon::parse($baseDate->format('Y-m-d') . ' ' . $eveningPeriod->end_time);

            // Handle overnight
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $eveningDurationInHours = $start->diffInHours($end);
        }

        // ✅ 2. Check for evening period (23:00 → next day 09:00)
        if (
            $eveningPeriod &&
            $checkIn->copy()->addHours($eveningDurationInHours)->toDateTimeString() === $checkOut->toDateTimeString() &&
            $checkInTime === $eveningPeriod->start_time &&
            $checkOutTime === $eveningPeriod->end_time
        ) {

            return [[
                'date' => $checkIn->toDateString(),
                'period_type' => 'evening',
                'period_id' => $eveningPeriod->id,
            ]];
        }

        // ✅ 3. Same day booking which will be only morning
        if ($checkIn->toDateString() === $checkOut->toDateString()) {
            return $this->handleSameDayBookingValidation($checkIn, $checkOut, $property);
        }

        // ✅ 4. Multi-day fallback
        $startDate = $checkIn->copy()->startOfDay();
        $endDate = $checkOut->copy()->startOfDay();

        $currentDate = $startDate->copy();
        $isFirstDay = true;

        while ($currentDate->lte($endDate)) {
            $isLastDay = $currentDate->equalTo($endDate);

            if ($isFirstDay) {
                $items = $this->handleFirstDayValidation($checkIn, $currentDate, $property, $isLastDay);
                $bookingItems = array_merge($bookingItems, $items);
            } elseif ($isLastDay) {
                $items = $this->handleLastDayValidation($checkOut, $currentDate, $property);
                $bookingItems = array_merge($bookingItems, $items);
            } else {
                if ($dayPeriod) {
                    $bookingItems[] = [
                        'date' => $currentDate->toDateString(),
                        'period_type' => 'day',
                        'period_id' => $dayPeriod->id,
                    ];
                }
                elseif ($morningPeriod && $eveningPeriod) {
                    // Add both morning and evening on each middle day
                    $bookingItems[] = [
                        'date' => $currentDate->toDateString(),
                        'period_type' => 'morning',
                        'period_id' => $morningPeriod->id,
                    ];
                    $bookingItems[] = [
                        'date' => $currentDate->toDateString(),
                        'period_type' => 'evening',
                        'period_id' => $eveningPeriod->id,
                    ];
                }
            }

            $currentDate->addDay();
            $isFirstDay = false;
        }

        return $bookingItems;
    }

    private function handleSameDayBookingValidation(Carbon $checkIn, Carbon $checkOut, $property)
    {
        //retrieve the time of check in and check out
        $checkInTime = $checkIn->format('H:i:s');
        $checkOutTime = $checkOut->format('H:i:s');

        // Check if it matches exactly one period that will be morning
        foreach ($property->periods as $period) {
            if ($checkInTime === $period->start_time && $checkOutTime === $period->end_time) {
                return [[
                    'date' => $checkIn->toDateString(),
                    'period_type' => $this->getPeriodTypeNameValidation($period->type),
                    'period_id' => $period->id,
                ]];
            }
        }

        return [];
    }

    private function handleFirstDayValidation(Carbon $checkIn, Carbon $currentDate, $property, bool $isAlsoLastDay)
    {
        $checkInTime = $checkIn->format('H:i:s');
        $periods = $property->periods->keyBy('type');
        $dayPeriod = $periods->get(3);
        $morningPeriod = $periods->get(1);
        $eveningPeriod = $periods->get(2);

        $items = [];

        // If check-in starts at morning time
        if ($morningPeriod && $checkInTime === $morningPeriod->start_time) {
            if (!$isAlsoLastDay && $dayPeriod) {
                $items[] = [
                    'date' => $currentDate->toDateString(),
                    'period_type' => 'day',
                    'period_id' => $dayPeriod->id,
                ];
            }
        }
        // If check-in starts at evening time
        elseif ($eveningPeriod && $checkInTime === $eveningPeriod->start_time) {
            if (!$isAlsoLastDay) {
                $items[] = [
                    'date' => $currentDate->toDateString(),
                    'period_type' => 'evening',
                    'period_id' => $eveningPeriod->id,
                ];
            }
        }
        // If check-in starts at daytime
        elseif ($dayPeriod && $checkInTime === $dayPeriod->start_time) {
            if (!$isAlsoLastDay) {
                $items[] = [
                    'date' => $currentDate->toDateString(),
                    'period_type' => 'day',
                    'period_id' => $dayPeriod->id,
                ];
            }
        }

        return $items;
    }

    private function handleLastDayValidation(Carbon $checkOut, Carbon $currentDate, $property)
    {
        $checkOutTime = $checkOut->format('H:i:s');
        $periods = $property->periods->keyBy('type');
        $morningPeriod = $periods->get(1);
        $eveningPeriod = $periods->get(2);

        $items = [];

        // Only match morning if checkOut ends at morning end time
        if ($morningPeriod && $checkOutTime === $morningPeriod->end_time) {
            $items[] = [
                'date' => $currentDate->toDateString(),
                'period_type' => 'morning',
                'period_id' => $morningPeriod->id,
            ];
        }

        return $items;
    }

    private function getPeriodTypeNameValidation($type)
    {
        return match($type) {
            1 => 'morning',
            2 => 'evening',
            3 => 'day',
            default => 'unknown'
        };
    }
    private function checkIfHasOnePeriod($property,Carbon $checkIn, Carbon $checkOut, Closure $fail)
    {
        $periods = $property->periods->keyBy('type');
        $dayPeriod = $periods->get(3);     // full day
        $morningPeriod = $periods->get(1); // morning
        $eveningPeriod = $periods->get(2); // evening
        if($morningPeriod && !$eveningPeriod && !$dayPeriod) {
            if($checkIn->toDateString() != $checkOut->toDateString()) {
                $fail(__('the checkin day should the same day because this property accept morning only'));
            }
            return [[
                'date' => $checkIn->toDateString(),
                'period_type' => 'morning',
                'period_id' => $morningPeriod->id,
            ]];
        }

        if($eveningPeriod && !$morningPeriod && !$dayPeriod) {
            $nextDay = $checkIn->copy()->addDay()->toDateString();
            if($checkIn->toDateString() == $nextDay) {
                $fail(__('the checkout day should the next day because this property accept evening only'));
            }
            return [[
                'date' => $checkIn->toDateString(),
                'period_type' => 'morning',
                'period_id' => $eveningPeriod->id,
            ]];
        }


    }
}
