<?php

namespace App\Repositories\Api\User;


use App\Http\Resources\AllPropertyReservationResource;
use App\Interfaces\Gateways\Api\User\PropertyReservationApiRepositoryInterface;
use App\Models\Property;
use App\Models\PropertyReservation;
use App\Notifications\Users\Host\ChangeStatusReservationNotification;
use App\Notifications\Users\Host\propertyReservationCreated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\PropertyReservationDetail;

class EloquentPropertyReservationApiRepository implements PropertyReservationApiRepositoryInterface
{
    public function checkAvailable($data)
    {
        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        if ($property->availabilities->isEmpty()) {
            return response()->json(['message' => 'No availability found for this property'], 404);
        }

        $firstAvailable = $property->availabilities->sortBy('availability_start_date')->first();
        $availabilityStart = \Carbon\Carbon::parse($firstAvailable->availability_start_date)->startOfDay();
        $today = now()->startOfDay();

        $start = $today->greaterThan($availabilityStart) ? $today : $availabilityStart;
        $end = (clone $start)->addDays(29);

        $responseMonth = $start->format('F');
        $responseYear = $start->year;

        $supportedTypes = [
            1 => 'morning',
            2 => 'evening',
            3 => 'day',
        ];

        $availableTypes = $property->periods->pluck('type')->unique();

        $periodTimeMap = [];
        foreach ($property->periods as $period) {
            $periodTimeMap[$period->type] = [
                'start' => $period->start_time,
                'end' => $period->end_time,
            ];
        }


        $reservationDetails = \App\Models\PropertyReservationDetail::whereHas('reservation', function ($q) use ($property) {
            $q->where('property_id', $property->id)
                ->where('status', '!=', 2);
        })
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('from_datetime', [$start, $end])
                    ->orWhereBetween('to_datetime', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('from_datetime', '<=', $start)->where('to_datetime', '>=', $end);
                    });
            })
            ->with('period')
            ->get();


        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dayInfo = [
                'date' => $cursor->toDateString(),
                'periods' => [],
            ];

            foreach ($availableTypes as $type) {
                $label = $supportedTypes[$type];

                if (!isset($periodTimeMap[$type])) {
                    $dayInfo['periods'][$label] = false;
                    continue;
                }

                $startTime = $periodTimeMap[$type]['start'];
                $endTime = $periodTimeMap[$type]['end'];

                $periodStart = \Carbon\Carbon::parse($cursor->toDateString() . ' ' . $startTime);
                $periodEnd = \Carbon\Carbon::parse($cursor->toDateString() . ' ' . $endTime);

                if ($periodEnd <= $periodStart) {
                    $periodEnd->addDay();
                }

                $conflictTypes = match ($type) {
                    1, 2 => [3, $type],
                    3 => [1, 2, 3],
                    default => [$type],
                };

                $isReserved = $reservationDetails->contains(function ($detail) use ($periodStart, $periodEnd, $conflictTypes) {
                    return in_array($detail->period->type, $conflictTypes) &&
                        $periodStart < $detail->to_datetime &&
                        $periodEnd > $detail->from_datetime;
                });

                $dayInfo['periods'][$label] = $isReserved;
            }

            $days[] = $dayInfo;
            $cursor->addDay();
        }

        return [
            'month' => $responseMonth,
            'year' => $responseYear,
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
        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        if ($property->availabilities->isEmpty()) {
            return response()->json(['message' => 'No availability found for this property'], 404);
        }

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];

        $month = $monthMap[strtolower($data['month'])] ?? null;
        $year = (int) $data['year'];

        if (!$month || !$year) {
            return response()->json(['message' => 'Invalid month or year'], 400);
        }

        $monthStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $today = now()->startOfDay();
        $start = $today->greaterThan($monthStart) ? $today : $monthStart;
        $end = (clone $start)->addDays(29); // 30-day window

        $supportedTypes = [
            1 => 'morning',
            2 => 'evening',
            3 => 'day',
        ];

        $availableTypes = $property->periods->pluck('type')->unique();

        // Dynamic period time map from DB
        $periodTimeMap = [];
        foreach ($property->periods as $period) {
            $periodTimeMap[$period->type] = [
                'start' => $period->start_time,
                'end' => $period->end_time,
            ];
        }

        $reservationDetails = PropertyReservationDetail::whereHas('reservation', function ($q) use ($property) {
            $q->where('property_id', $property->id)
                ->where('status', '!=', 2);
        })
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('from_datetime', [$start, $end])
                    ->orWhereBetween('to_datetime', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('from_datetime', '<=', $start)->where('to_datetime', '>=', $end);
                    });
            })
            ->with('period')
            ->get();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dayInfo = [
                'date' => $cursor->toDateString(),
                'periods' => [],
            ];

            foreach ($availableTypes as $type) {
                $label = $supportedTypes[$type];

                if (!isset($periodTimeMap[$type])) {
                    $dayInfo['periods'][$label] = false;
                    continue;
                }

                $startTime = $periodTimeMap[$type]['start'];
                $endTime = $periodTimeMap[$type]['end'];

                $periodStart = \Carbon\Carbon::parse($cursor->toDateString() . ' ' . $startTime);

                if ($endTime <= $startTime) {
                    $periodEnd = \Carbon\Carbon::parse($cursor->copy()->addDay()->toDateString() . ' ' . $endTime);
                } else {
                    $periodEnd = \Carbon\Carbon::parse($cursor->toDateString() . ' ' . $endTime);
                }

                $conflictTypes = match ($type) {
                    1, 2 => [3, $type],
                    3 => [1, 2, 3],
                    default => [$type],
                };

                $isReserved = $reservationDetails->contains(function ($detail) use ($periodStart, $periodEnd, $conflictTypes) {
                    return in_array($detail->period->type, $conflictTypes) &&
                        $periodStart < $detail->to_datetime &&
                        $periodEnd > $detail->from_datetime;
                });

                $dayInfo['periods'][$label] = $isReserved;
            }

            $days[] = $dayInfo;
            $cursor->addDay();
        }

        return [
            'month' => ucfirst($data['month']),
            'year' => $year,
            'days' => $days,
        ];
    }

    protected function convertMonthNameToInt(string $month): ?int
    {
        $month = strtolower($month);
        $map = [
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12,
        ];

        return $map[$month] ?? null;
    }
    public function CheckPrice($data): array
    {
        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $pattern = (new \App\Rules\CheckIfDateExistsInPropertyAndAvailableRule())
            ->setData($data)
            ->analyzeBookingPattern($checkIn, $checkOut, $property);

        $totalPrice = 0;
        $details = [];

        foreach ($pattern as $item) {
            $date = $item['date'];
            $periodId = $item['period_id'];
            $periodType = $item['period_type'];
            $dayOfWeek = Carbon::parse($date)->format('l');

            $price = null;
            $source = null;

            $matchingAvailabilities = $property->availabilities->filter(function ($availability) use ($date) {
                $cursor = Carbon::parse($date);
                return $cursor->between($availability->availability_start_date, $availability->availability_end_date);
            });

            // Prioritize child overrides (availability with parent_id set), fallback to parent
            $sortedAvailabilities = $matchingAvailabilities->sortBy(function ($a) {
                return $a->parent_id ? 0 : 1; // child (override) first
            });

            foreach ($sortedAvailabilities as $availability) {
                $day = $availability->availabilityDays->first(function ($day) use ($dayOfWeek, $periodId) {
                    return $day->day_of_week === $dayOfWeek && $day->property_period_id == $periodId;
                });

                if ($day) {
                    $price = (float) $day->price;
                    $source = $availability->parent_id ? 'override' : 'default';
                    break;
                }
            }

            if ($price === null) {
                throw new \Exception("No price found for date {$date} and period type {$periodType}");
            }

            $totalPrice += $price;

            $details[] = [
                'date' => $date,
                'period_type' => $periodType,
                'price' => number_format($price, 2, '.', ''),
                'source' => $source
            ];
        }

        return [
            'total_price' => round($totalPrice, 2),
            'details' => $details
        ];
    }
    public function makeReservation($data)
    {
        $user = Auth::guard('api')->user();

        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $pattern = (new \App\Rules\CheckIfDateExistsInPropertyAndAvailableRule())
            ->setData($data)
            ->analyzeBookingPattern($checkIn, $checkOut, $property);

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $reservationDetails = [];

            foreach ($pattern as $item) {
                $date = $item['date'];
                $periodType = $item['period_type'];
                $periodId = $item['period_id'];
                $dateEnd = $date;
                if($periodType == 'evening'|| $periodType == 'day'){
                    $dateEnd = Carbon::parse($date)->addDay()->format('Y-m-d');
                }

                $period = $property->periods->firstWhere('id', $periodId);
                if (!$period) {
                    throw new \Exception("Invalid period in booking pattern.");
                }

                $dayOfWeek = Carbon::parse($date)->format('l');

                // Check for child availability (override)
                $availability = $property->availabilities
                    ->filter(fn($a) => Carbon::parse($date)->between($a->availability_start_date, $a->availability_end_date))
                    ->sortBy(fn($a) => $a->parent_id ? 0 : 1) // prioritize child (override)
                    ->first(fn($a) =>
                    $a->availabilityDays->contains(fn($d) =>
                        $d->property_period_id == $periodId && $d->day_of_week === $dayOfWeek
                    )
                    );

                if (!$availability) {
                    throw new \Exception("No availability found for {$date} ({$periodType})");
                }

                $day = $availability->availabilityDays->first(fn($d) =>
                    $d->property_period_id == $periodId && $d->day_of_week === $dayOfWeek
                );

                if (!$day) {
                    throw new \Exception("No price found for {$date} and period {$periodType}");
                }

                $price = (float) $day->price;
                $totalPrice += $price;

                // Build reservation detail
                $fromDateTime = Carbon::parse($date . ' ' . $period->start_time);
                $toDateTime = Carbon::parse($dateEnd . ' ' . $period->end_time);
                if ($toDateTime->lessThan($fromDateTime)) {
                    $toDateTime->addDay(); // overnight
                }

                $reservationDetails[] = [
                    'property_period_id' => $periodId,
                    'from_datetime' => $fromDateTime,
                    'to_datetime' => $toDateTime,
                    'price' => $price,
                ];
            }

            // Create main reservation
            $reservation = PropertyReservation::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'contact_info' => $data['contact_info'],
                'check_in' => $checkIn->toDateTimeString(),
                'check_out' => $checkOut->toDateTimeString(),
                'total_price' => round($totalPrice, 2),
                'status' => 0,
            ]);

            // Create details
            foreach ($reservationDetails as $detail) {
                $reservation->details()->create($detail);
            }

//            // Notify the host
//            $host = $property->host;
//            Notification::send($host, new propertyReservationCreated($reservation));
//
//            $providerLang = $host->lang;
//            $notificationData = [
//                'notification' => [
//                    'title' => Lang::get('app.notifications.new-property-reservation-title', [], $providerLang),
//                    'body' => Lang::get('app.notifications.new-property-reservation-body', [
//                        'username' => $reservation->user->username,
//                        'reservation_id' => $reservation->id,
//                    ], $providerLang),
//                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
//                    'sound' => 'default',
//                ],
//                'data' => [
//                    'type' => 'property_reservation',
//                    'slug' => $property->slug,
//                    'property_id' => $property->id,
//                    'reservation_id' => $reservation->id,
//                ],
//            ];
//
//            $tokens = $host->DeviceTokenMany->pluck('token')->toArray();
//            if (!empty($tokens)) {
//                sendNotification($tokens, $notificationData);
//            }

            DB::commit();

            return [
                'reservation_id' => $reservation->id,
                'total_price' => $reservation->total_price,
                'status' => 'pending',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }
    public function updateReservation( $data)
    {
        $user = Auth::guard('api')->user();

        $reservation = PropertyReservation::where('id', $data['reservation_id'])
            ->where('user_id', $user->id)
            ->with(['property.availabilities.availabilityDays.period', 'property.periods'])
            ->firstOrFail();

        $property = $reservation->property;

        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $pattern = (new \App\Rules\CheckIfDateExistsInPropertyAndAvailableRule())
            ->setData($data)
            ->analyzeBookingPattern($checkIn, $checkOut, $property);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $details = [];

            foreach ($pattern as $item) {
                $date = $item['date'];
                $periodId = $item['period_id'];

                $period = $property->periods->firstWhere('id', $periodId);
                if (!$period) {
                    throw new \Exception("Invalid period ID: $periodId");
                }

                $dayOfWeek = Carbon::parse($date)->format('l');

                // Get the right availability (child first, fallback to parent)
                $availability = $property->availabilities
                    ->filter(fn($a) => Carbon::parse($date)->between($a->availability_start_date, $a->availability_end_date))
                    ->sortBy(fn($a) => $a->parent_id ? 0 : 1)
                    ->first(fn($a) =>
                    $a->availabilityDays->contains(fn($d) =>
                        $d->property_period_id == $periodId && $d->day_of_week === $dayOfWeek
                    )
                    );

                if (!$availability) {
                    throw new \Exception("No availability found for $date");
                }

                $day = $availability->availabilityDays->first(fn($d) =>
                    $d->property_period_id == $periodId && $d->day_of_week === $dayOfWeek
                );

                if (!$day) {
                    throw new \Exception("No price found for $date");
                }

                $price = (float) $day->price;
                $totalPrice += $price;

                $fromDateTime = Carbon::parse($date . ' ' . $period->start_time);
                $toDateTime = Carbon::parse($date . ' ' . $period->end_time);
                if ($toDateTime->lessThan($fromDateTime)) {
                    $toDateTime->addDay();
                }

                $details[] = [
                    'property_period_id' => $period->id,
                    'from_datetime' => $fromDateTime,
                    'to_datetime' => $toDateTime,
                    'price' => $price,
                ];
            }

            // Update main reservation
            $reservation->update([
                'check_in' => $checkIn->toDateTimeString(),
                'check_out' => $checkOut->toDateTimeString(),
                'contact_info' => $data['contact_info'],
                'total_price' => round($totalPrice, 2),
            ]);

            // Delete old details and recreate
            $reservation->details()->delete();
            foreach ($details as $detail) {
                $reservation->details()->create($detail);
            }

            DB::commit();

            return [
                'reservation_id' => $reservation->id,
                'total_price' => $reservation->total_price,
                'check_in' => $reservation->check_in,
                'check_out' => $reservation->check_out,
                'status' => 'updated',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }
    public function deleteReservation($id)
    {
        DB::beginTransaction();

        try {
            $reservation = PropertyReservation::findOrFail($id);

            if ($reservation) {
                DatabaseNotification::where('type', 'App\Notifications\Users\Host\propertyReservationCreated')
                    ->whereJsonContains('data->options->reservation_id', $reservation->id)
                    ->delete();
            }

            $reservation->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e; // or return a proper error response
        }
    }

    public function allPropertyReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $property = Property::findBySlug($slug);
        $user = Auth::guard('api')->user();
        $reservations = PropertyReservation::where('property_id', $property->id)->where('user_id', $user->id)->paginate($perPage);
        $reservationsArray = $reservations->toArray();
        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];
        return [
            'count'=> PropertyReservation::where('property_id', $property->id)->where('user_id', $user->id)->count(),
            'reservations'=>AllPropertyReservationResource::collection($reservations),
            'pagination' => $pagination
        ];
    }

    public function allReservations()
    {
        $perPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();
        $reservations = PropertyReservation::where('user_id', $user->id)->paginate($perPage);
        $reservationsArray = $reservations->toArray();
        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];
        return [
            'count'=> PropertyReservation::where('user_id', $user->id)->count(),
            'reservations'=> AllPropertyReservationResource::collection($reservations),
            'pagination' => $pagination
        ];
    }

    public function changeStatusReservation($data)
    {
        $statusMap = [
            'confirmed' => 1,
            'cancelled' => 2,
        ];

        DB::beginTransaction();

        try {
            $reservationId = $data['id'];
            $statusLabel = $data['status'];

            $reservation = PropertyReservation::findOrFail($reservationId);
            $reservation->status = $statusMap[$statusLabel];
            $reservation->save();

            $user = $reservation->user;
            Notification::send($user, new ChangeStatusReservationNotification($reservation));

            $userLang = $user->lang;
            $property = $reservation->property;

            $notificationData = [
                'notification' => [
                    'title' => Lang::get('app.notifications.reservation-status-updated-title', [], $userLang),
                    'body'  => Lang::get('app.notifications.reservation-status-updated-body', [
                        'reservation_id' => $reservation->id,
                        'status'         => $statusLabel === 'confirmed'
                            ? Lang::get('app.notifications.status-confirmed', [], $userLang)
                            : Lang::get('app.notifications.status-cancelled', [], $userLang),
                    ], $userLang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default',
                ],
                'data' => [
                    'type'           => 'property_reservation',
                    'slug'           => $property->slug,
                    'property_id'    => $property->id,
                    'reservation_id' => $reservation->id,
                    'new_status'     => $statusLabel,
                ],
            ];

            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            DB::commit();

            return $reservation;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e; // or return ['error' => 'Failed to update reservation status.']
        }
    }

    public function RequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $property = Property::findBySlug($slug);
        $reservations = PropertyReservation::where('property_id', $property->id)->where('status', 0)->paginate($perPage);
        $reservationsArray = $reservations->toArray();
        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];
        return [
            'count'=> PropertyReservation::where('property_id', $property->id)->where('status', 0)->count(),
            'reservations'=>AllPropertyReservationResource::collection($reservations),
            'pagination' => $pagination
        ];
    }

    public function approvedRequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $property = Property::findBySlug($slug);
        $reservations = PropertyReservation::where('property_id', $property->id)->where('status', 1)->paginate($perPage);
        $reservationsArray = $reservations->toArray();
        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        return [
            'count'=> PropertyReservation::where('property_id', $property->id)->where('status', 1)->count(),
            'reservations'=>AllPropertyReservationResource::collection($reservations),
            'pagination' => $pagination
        ];
    }
}


