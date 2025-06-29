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

        $periodType = $this->resolvePeriodType($data['period_type']);

        $firstAvailability = $property->availabilities
            ->filter(fn($a) => $a->availabilityDays->where('period.type', $periodType)->isNotEmpty())
            ->sortBy('availability_start_date')
            ->first();

        if (!$firstAvailability) {
            return response()->json(['message' => 'No availability found for this period.'], 404);
        }

        $start = \Carbon\Carbon::parse($firstAvailability->availability_start_date)->startOfMonth();
        $end = (clone $start)->addMonthNoOverflow()->endOfMonth();

        // Handle conflict logic
        $conflictTypes = match ($periodType) {
            1, 2 => [3, $periodType],  // morning/evening => also conflict with day
            3     => [1, 2, 3],        // day => conflict with all types
            default => [$periodType],
        };

        $periodIds = $property->periods
            ->whereIn('type', $conflictTypes)
            ->pluck('id');

        if ($periodIds->isEmpty()) {
            return response()->json(['message' => 'This period is not supported for this property.'], 422);
        }

        // Exclude cancelled (status = 2) reservations
        $reservations = \App\Models\PropertyReservation::where('property_id', $property->id)
            ->whereIn('property_period_id', $periodIds)
            ->where('status', '!=', 2)
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
        $requestedType = $this->resolvePeriodType($data['period_type']);

        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        // 🟡 Conflict logic: if "day", conflict with both "morning" and "evening" too
        $conflictTypes = in_array($requestedType, [1, 2]) ? [$requestedType, 3] : [1, 2, 3];

        $periodIds = $property->periods
            ->whereIn('type', $conflictTypes)
            ->pluck('id');

        if ($periodIds->isEmpty()) {
            return response()->json(['message' => 'This period is not supported for this property.'], 422);
        }

        // ✅ Only fetch availability matching the *requested* type (not all conflict types)
        $firstAvailable = $property->availabilities
            ->filter(function ($availability) use ($requestedType) {
                return $availability->availabilityDays->contains(fn($day) => $day->period->type === $requestedType);
            })
            ->sortBy('availability_start_date')
            ->first();

        if (! $firstAvailable) {
            return response()->json(['message' => 'No availability found for this period.'], 404);
        }

//        $start = \Carbon\Carbon::parse($firstAvailable->availability_start_date)->greaterThanOrEqualTo(now())
//            ? \Carbon\Carbon::parse($firstAvailable->availability_start_date)->startOfDay()
//            : now()->startOfDay();

        $monthMap = [
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

        $monthName = strtolower($data['month']);
        $month = $monthMap[$monthName] ?? null;
        $year = (int) $data['year'];
        $start = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = (clone $start)->addDays(29); // Show 30 days

        // 👇 Exclude cancelled (status = 2)
        $reservations = \App\Models\PropertyReservation::where('property_id', $property->id)
            ->whereIn('property_period_id', $periodIds)
            ->where('status', '!=', 2)
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

    public function CheckPrice($data)
    {
        $property = Property::where('slug', $data['property_slug'])
            ->with(['availabilities.availabilityDays.period', 'periods'])
            ->firstOrFail();

        $periodMap = [
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        ];

        $periodType = $periodMap[$data['period_type']] ?? null;
        if (!$periodType) {
            return response()->json(['message' => 'Invalid period type.'], 422);
        }

        $periodIds = $property->periods->where('type', $periodType)->pluck('id');
        if ($periodIds->isEmpty()) {
            return response()->json(['message' => 'Period not available for this property.'], 422);
        }

        $checkIn = \Carbon\Carbon::parse($data['check_in']);
        $checkOut = \Carbon\Carbon::parse($data['check_out']);
        $cursor = $checkIn->copy();
        $totalPrice = 0;

        while ($cursor->lte($checkOut)) {
            $dayOfWeek = $cursor->format('l');
            $date = $cursor->toDateString();

            $price = null;
            $source = null;

            // Check child availability first
            $childAvailability = $property->availabilities
                ->whereNotNull('parent_id')
                ->first(function ($availability) use ($cursor, $periodIds, $dayOfWeek) {
                    return $cursor->between($availability->availability_start_date, $availability->availability_end_date) &&
                        $availability->availabilityDays->contains(function ($day) use ($periodIds, $dayOfWeek) {
                            return $periodIds->contains($day->property_period_id) && $day->day_of_week === $dayOfWeek;
                        });
                });

            if ($childAvailability) {
                $day = $childAvailability->availabilityDays->first(function ($d) use ($periodIds, $dayOfWeek) {
                    return $periodIds->contains($d->property_period_id) && $d->day_of_week === $dayOfWeek;
                });
                $price = $day?->price;
                $source = 'child';
            }

            // If not found, check parent
            if ($price === null) {
                $parentAvailability = $property->availabilities
                    ->whereNull('parent_id')
                    ->first(function ($availability) use ($cursor, $periodIds, $dayOfWeek) {
                        return $cursor->between($availability->availability_start_date, $availability->availability_end_date) &&
                            $availability->availabilityDays->contains(function ($day) use ($periodIds, $dayOfWeek) {
                                return $periodIds->contains($day->property_period_id) && $day->day_of_week === $dayOfWeek;
                            });
                    });

                if ($parentAvailability) {
                    $day = $parentAvailability->availabilityDays->first(function ($d) use ($periodIds, $dayOfWeek) {
                        return $periodIds->contains($d->property_period_id) && $d->day_of_week === $dayOfWeek;
                    });
                    $price = $day?->price;
                    $source = 'parent';
                }
            }

            $totalPrice += $price ?? 0;
            $cursor->addDay();
        }

        return [
            'total_price' => round($totalPrice, 2),
        ];
    }

    public function makeReservation($data)
    {
        $user = Auth::guard('api')->user();

        DB::beginTransaction();

        try {
            $property = Property::where('slug', $data['property_slug'])
                ->with(['availabilities.availabilityDays.period', 'periods'])
                ->firstOrFail();

            $periodMap = [
                'morning' => 1,
                'evening' => 2,
                'day'     => 3,
            ];

            $periodType = $periodMap[$data['period_type']] ?? null;
            if (!$periodType) {
                throw new \Exception('Invalid period type.');
            }

            $period = $property->periods->firstWhere('type', $periodType);
            if (!$period) {
                throw new \Exception('Period not available for this property.');
            }

            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $cursor = $checkIn->copy();
            $totalPrice = 0;

            while ($cursor->lte($checkOut)) {
                $dayOfWeek = $cursor->format('l');

                $childAvailability = $property->availabilities
                    ->whereNotNull('parent_id')
                    ->first(function ($availability) use ($cursor, $period, $dayOfWeek) {
                        return $cursor->between($availability->availability_start_date, $availability->availability_end_date) &&
                            $availability->availabilityDays->contains(
                                fn($day) =>
                                $day->property_period_id === $period->id && $day->day_of_week === $dayOfWeek
                            );
                    });

                if ($childAvailability) {
                    $day = $childAvailability->availabilityDays->first(
                        fn($d) =>
                        $d->property_period_id === $period->id && $d->day_of_week === $dayOfWeek
                    );
                    $totalPrice += $day?->price ?? 0;
                } else {
                    $parentAvailability = $property->availabilities
                        ->whereNull('parent_id')
                        ->first(function ($availability) use ($cursor, $period, $dayOfWeek) {
                            return $cursor->between($availability->availability_start_date, $availability->availability_end_date) &&
                                $availability->availabilityDays->contains(
                                    fn($day) =>
                                    $day->property_period_id === $period->id && $day->day_of_week === $dayOfWeek
                                );
                        });

                    if ($parentAvailability) {
                        $day = $parentAvailability->availabilityDays->first(
                            fn($d) =>
                            $d->property_period_id === $period->id && $d->day_of_week === $dayOfWeek
                        );
                        $totalPrice += $day?->price ?? 0;
                    }
                }

                $cursor->addDay();
            }

            $reservation = PropertyReservation::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'property_period_id' => $period->id,
                'contact_info' => $data['contact_info'],
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'total_price' => round($totalPrice, 2),
                'status' => 0,
            ]);

            // Optionally notify provider
            $host = $property->host;
            Notification::send($host, new propertyReservationCreated($reservation));

            $providerLang = $host->lang;
            $notificationData = [
                'notification' => [
                    'title' => Lang::get('app.notifications.new-property-reservation-title', [], $providerLang),
                    'body'  => Lang::get('app.notifications.new-property-reservation-body', [
                        'username'        => $reservation->user->username,
                        'reservation_id'  => $reservation->id,
                    ], $providerLang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default'
                ],
                'data' => [
                    'type'           => 'property_reservation',
                    'slug'           => $property->slug,
                    'property_id'     => $property->id,
                    'reservation_id' => $reservation->id
                ]
            ];

            $tokens = $host->DeviceTokenMany->pluck('token')->toArray();

            if (!empty($tokens))
                sendNotification($tokens, $notificationData);

            DB::commit();

            return [
                'reservation_id' => $reservation->id,
                'total_price' => $reservation->total_price,
                'status' => 'pending',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e; // or return ['error' => 'Reservation failed.']
        }
    }

    public function updateReservation($data)
    {
        $reservation = \App\Models\PropertyReservation::with('property.periods', 'property.availabilities.availabilityDays.period')
            ->findOrFail($data['id']);

        $property = $reservation->property;

        $periodMap = [
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        ];

        $periodType = $periodMap[$data['period_type']] ?? null;
        if (!$periodType) {
            throw new \Exception('Invalid period type.');
        }

        $period = $property->periods->firstWhere('type', $periodType);
        if (!$period) {
            throw new \Exception('Period not available for this property.');
        }

        $checkIn = \Carbon\Carbon::parse($data['check_in']);
        $checkOut = \Carbon\Carbon::parse($data['check_out']);
        $cursor = $checkIn->copy();
        $totalPrice = 0;

        while ($cursor->lte($checkOut)) {
            $dayOfWeek = $cursor->format('l');

            // Prefer child availability
            $childAvailability = $property->availabilities
                ->whereNotNull('parent_id')
                ->first(function ($availability) use ($cursor, $period, $dayOfWeek) {
                    return $cursor->between($availability->availability_start_date, $availability->availability_end_date)
                        && $availability->availabilityDays->contains(function ($day) use ($period, $dayOfWeek) {
                            return $day->property_period_id === $period->id && $day->day_of_week === $dayOfWeek;
                        });
                });

            if ($childAvailability) {
                $day = $childAvailability->availabilityDays
                    ->first(fn($d) => $d->property_period_id === $period->id && $d->day_of_week === $dayOfWeek);
                $totalPrice += $day?->price ?? 0;
            } else {
                // Fallback to parent availability
                $parentAvailability = $property->availabilities
                    ->whereNull('parent_id')
                    ->first(function ($availability) use ($cursor, $period, $dayOfWeek) {
                        return $cursor->between($availability->availability_start_date, $availability->availability_end_date)
                            && $availability->availabilityDays->contains(function ($day) use ($period, $dayOfWeek) {
                                return $day->property_period_id === $period->id && $day->day_of_week === $dayOfWeek;
                            });
                    });

                if ($parentAvailability) {
                    $day = $parentAvailability->availabilityDays
                        ->first(fn($d) => $d->property_period_id === $period->id && $d->day_of_week === $dayOfWeek);
                    $totalPrice += $day?->price ?? 0;
                }
            }

            $cursor->addDay();
        }

        // ✅ Update reservation
        $reservation->update([
            'property_period_id' => $period->id,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'contact_info' => $data['contact_info'],
            'total_price' => round($totalPrice, 2),
            'status' => 0, // still pending
        ]);

        return [
            'reservation_id' => $reservation->id,
            'total_price' => $reservation->total_price,
            'status' => 'pending (updated)',
        ];
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

