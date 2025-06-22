<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\PropertyReservationApiRepositoryInterface;
use App\Models\Property;
use App\Models\Service;
use App\Models\ServiceReservationDetail;
use Illuminate\Support\Carbon;


class EloquentPropertyReservationApiRepository implements PropertyReservationApiRepositoryInterface
{
    public function checkAvailable($data)
    {
        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period'])
            ->firstOrFail();

        if ($property->availabilities->isEmpty()) {
            return response()->json(['message' => 'No availability found for this property'], 404);
        }

        $periodType = $this->resolvePeriodType($data['period_type']);

        $firstAvailability = $property->availabilities
            ->filter(fn($a) => $a->availabilityDays->where('period.type', $periodType)->isNotEmpty())
            ->sortBy('availability_start_date')
            ->first();

        $start = \Carbon\Carbon::parse($firstAvailability->availability_start_date)->startOfMonth();
        $end = (clone $start)->addMonthNoOverflow()->endOfMonth();

        $periodIds = $property->periods->where('type', $periodType)->pluck('id');

        // Get all reservations for this property/period between the date range
        $reservations = \App\Models\PropertyReservation::where('property_id', $property->id)
            ->whereIn('property_period_id', $periodIds)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('check_in', [$start, $end])
                    ->orWhereBetween('check_out', [$start, $end]);
            })
            ->get();

        // Build response per day
        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->gte(now()->startOfDay())) {
                $day = $cursor->toDateString();

                $isReserved = $reservations->contains(function ($reservation) use ($cursor) {
                    return $cursor->between($reservation->check_in, $reservation->check_out);
                });

                $days[] = [
                    'date' => $day,
                    'reserved' => $isReserved,
                ];
            }
            $cursor->addDay();
        }


        return [
            'month' => $start->format('F'),
            'year' => $start->year,
            'days' => $days,
        ];
    }


    protected function resolvePeriodType(string $type): int
    {
        return match ($type) {
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        };
    }

    public function checkAvailableMonth($data)
    {
        $periodType = $this->resolvePeriodType($data['period_type']);

        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        $periodIds = $property->periods
            ->where('type', $periodType)
            ->pluck('id');

        if ($periodIds->isEmpty()) {
            return response()->json(['message' => 'This period is not supported for this property.'], 422);
        }

        // Find the first available day for the given period type
        $firstAvailable = $property->availabilities
            ->filter(function ($availability) use ($periodType) {
                return $availability->availabilityDays->contains(fn($day) => $day->period->type === $periodType);
            })
            ->sortBy('availability_start_date')
            ->first();

        if (! $firstAvailable) {
            return response()->json(['message' => 'No availability found for this period.'], 404);
        }

        $start = \Carbon\Carbon::parse($firstAvailable->availability_start_date)->greaterThanOrEqualTo(now())
            ? \Carbon\Carbon::parse($firstAvailable->availability_start_date)->startOfDay()
            : now()->startOfDay();

        $end = (clone $start)->addDays(29); // total 30 days

        // Get reservations for that period
        $reservations = \App\Models\PropertyReservation::where('property_id', $property->id)
            ->whereIn('property_period_id', $periodIds)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('check_in', [$start, $end])
                    ->orWhereBetween('check_out', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('check_in', '<=', $start)->where('check_out', '>=', $end);
                    });
            })
            ->get();

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $isReserved = $reservations->contains(function ($reservation) use ($cursor) {
                return $cursor->between($reservation->check_in, $reservation->check_out);
            });

            $days[] = [
                'date' => $date,
                'reserved' => $isReserved,
            ];

            $cursor->addDay();
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
        ];
    }
    protected function convertMonthNameToInt(string $month): ?int
    {
        $month = strtolower($month);
        $map = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];

        return $map[$month] ?? null;
    }




}
