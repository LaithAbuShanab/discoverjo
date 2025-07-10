<?php

namespace App\Repositories\Api\User;


use App\Http\Resources\UserSingleServiceReservationResource;
use App\Interfaces\Gateways\Api\User\ReservationApiRepositoryInterface;
use App\Models\Service;
use App\Models\ServiceReservation;
use App\Models\ServiceReservationDetail;
use App\Notifications\Users\Service\ChangeStatusReservationNotification;
use App\Notifications\Users\Service\ServiceReservationCreated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class EloquentReservationApiRepository implements ReservationApiRepositoryInterface
{
    public function reservationDate($data)
    {
        $service = Service::findBySlug($data['service_slug']);
        $date = trim($data["date"]);
        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = $dateCarbon->format('l'); // e.g., 'Friday'

        $booking = $service->serviceBookings()->first();
        if (!$booking) {
            return [];
        }

        $bookingDay = $booking->serviceBookingDays()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$bookingDay) {
            return [];
        }

        $startTime = Carbon::parse($bookingDay->opening_time);
        $endTime = Carbon::parse($bookingDay->closing_time);
        $duration = $booking->session_duration;
        $capacity = $booking->session_capacity;

        // dd($startTime, $endTime, $duration, $capacity);
        $slots = [];

        while ($startTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $startTime->format('H:i');
            $slotEnd = $startTime->copy()->addMinutes($duration)->format('H:i');

            // Sum quantity from reservation details via reservations on this date and slot
            $reservedQuantity = ServiceReservationDetail::whereHas('reservation', function ($query) use ($service, $date, $slotStart) {
                $query->where('service_id', $service->id)
                    ->where('date', $date)
                    ->where('start_time', $slotStart)
                    ->whereNot('status', 2); // Optional: only confirmed reservations
            })->sum('quantity');


            $available = max($capacity - $reservedQuantity, 0);

            $slots[] = [
                'start' => $slotStart,
                'end' => $slotEnd,
                'capacity'  => (int) $capacity,
                'reserved'  => (int) $reservedQuantity,
                'available' => (int) $available,
            ];

            $startTime->addMinutes($duration);
        }

        return $slots;
    }

    public function serviceReservation($data)
    {
        return DB::transaction(function () use ($data) {
            $service = Service::with('priceAges')->where('slug', $data['service_slug'])->firstOrFail();
            $userId = auth('api')->id();

            $totalPrice = 0;
            $detailsData = [];

            foreach ($data['reservations'] as $entry) {
                $type = $entry['reservation_detail']; // 1 = adult, 2 = child
                $quantity = $entry['quantity'];
                $priceAgeId = $entry['price_age_id'] ?? null;

                // Determine unit price
                if ($type === 2 && $priceAgeId) {
                    $priceAge = $service->priceAges->firstWhere('id', $priceAgeId);
                    $unitPrice = $priceAge?->price ?? 0;
                } else {
                    $unitPrice = $service->price;
                }

                $subtotal = $unitPrice * $quantity;
                $totalPrice += $subtotal;

                $detailsData[] = [
                    'reservation_detail' => $type,
                    'quantity' => $quantity,
                    'price_age_id' => $priceAgeId,
                    'price_per_unit' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Create the main reservation
            $reservation = $service->reservations()->create([
                'user_id' => $userId,
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'contact_info' => $data['contact_info'],
                'status' => 0,
                'total_price' => $totalPrice,
            ]);

            // Attach details
            foreach ($detailsData as $detail) {
                $reservation->details()->create($detail);
            }

            // Optionally notify provider
            $provider = $service->provider;
            Notification::send($provider, new ServiceReservationCreated($reservation));

            $providerLang = $provider->lang;
            $notificationData = [
                'notification' => [
                    'title' => Lang::get('app.notifications.new-service-reservation-title', [], $providerLang),
                    'body'  => Lang::get('app.notifications.new-service-reservation-body', [
                        'username'        => $reservation->user->username,
                        'reservation_id'  => $reservation->id,
                    ], $providerLang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default'
                ],
                'data' => [
                    'type'           => 'service_reservation',
                    'slug'           => $service->slug,
                    'service_id'     => $service->id,
                    'reservation_id' => $reservation->id
                ]
            ];

            $tokens = $provider->DeviceTokenMany->pluck('token')->toArray();

            if (!empty($tokens))
                sendNotification($tokens, $notificationData);

            // Notify an admin about the new user registration
            ProviderNotification(
                'new-service-reservation-title',
                'new-service-reservation-body',
                [
                    'action' => 'View',
                    'action_label' => 'view-reservation',
                    'action_url' => route('filament.provider.resources.service-reservations.view', $reservation),
                    'icon' => 'heroicon-o-bell-alert',
                    'color' => 'primary',
                    'view_data' => [
                        'reservation_id' => $reservation->id,
                        'reservation_username' => $reservation->user->username,
                        'api' => true
                    ]
                ],
                $provider
            );

            return $reservation;
        });
    }

    public function UserServiceReservations($data)
    {
        $service  = Service::findBySlug($data['service_slug']);
        $user = Auth::guard('api')->user();
        $reservations = ServiceReservation::where('service_id', $service->id)->where('user_id', $user->id)->get();
        return [
            "count"=> count($reservations),
            "reservations"=>UserSingleServiceReservationResource::collection($reservations)
        ];
    }

    public function allReservations()
    {
        $perPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();
        $reservations = ServiceReservation::where('user_id', $user->id)->paginate($perPage);
        $reservationsArray = $reservations->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'count'=>  ServiceReservation::where('user_id', $user->id)->count(),
            'reservations' => UserSingleServiceReservationResource::collection($reservations),
            'pagination' => $pagination
        ];
    }

    public function deleteReservation($id)
    {
        ServiceReservation::find($id)->delete();
    }

    public function updateReservation($data)
    {
        $reservation = \App\Models\ServiceReservation::with('service', 'details')
            ->where('id', $data['id'])
            ->where('user_id', auth('api')->id())
            ->firstOrFail();

        $service = $reservation->service;

        $totalPrice = 0;
        $detailsData = [];

        foreach ($data['reservations'] as $entry) {
            $type = $entry['reservation_detail'];
            $quantity = $entry['quantity'];
            $priceAgeId = $entry['price_age_id'] ?? null;

            if ($type === 2 && $priceAgeId) {
                $priceAge = $service->priceAges->firstWhere('id', $priceAgeId);
                $unitPrice = $priceAge?->price ?? 0;
            } else {
                $unitPrice = $service->price;
            }

            $subtotal = $unitPrice * $quantity;
            $totalPrice += $subtotal;

            $detailsData[] = [
                'reservation_detail' => $type,
                'quantity' => $quantity,
                'price_age_id' => $priceAgeId,
                'price_per_unit' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        // Update reservation fields
        $reservation->update([
//            'date' => $data['date'],
//            'start_time' => $data['start_time'],
            'contact_info' => $data['contact_info'],
            'total_price' => $totalPrice,
            'status'=>0
        ]);

        // Remove old details
        $reservation->details()->delete();

        // Re-create updated details
        foreach ($detailsData as $detail) {
            $reservation->details()->create($detail);
        }

        return $reservation;
    }

    public function changeStatusReservation($data)
    {
        return DB::transaction(function () use ($data) {
            $statusMap = [
                'confirmed' => 1,
                'cancelled' => 2,
            ];

            $reservationId = $data['id'];
            $statusLabel = $data['status'];

            $reservation = ServiceReservation::findOrFail($reservationId);
            $reservation->status = $statusMap[$statusLabel];
            $reservation->save();

            $user = $reservation->user;
            Notification::send($user, new ChangeStatusReservationNotification($reservation));

            $userLang = $user->lang;
            $service = $reservation->service;

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
                    'type'           => 'service_reservation',
                    'slug'           => $service->slug,
                    'service_id'     => $service->id,
                    'reservation_id' => $reservation->id,
                    'new_status'     => $statusLabel,
                ],
            ];

            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            // Return reservation OR send this notificationData via FCM if needed
            return $reservation;
        });
    }

    public function providerRequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $service = Service::findBySlug($slug);
        $requests = ServiceReservation::where('service_id', $service->id)->where('status', 0)->paginate($perPage);

        $reservationsArray = $requests->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'count'=>ServiceReservation::where('service_id', $service->id)->where('status', 0)->count(),
            'reservations' => UserSingleServiceReservationResource::collection($requests),
            'pagination' => $pagination
        ];
    }

    public function approvedRequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $service = Service::findBySlug($slug);
        $requests = ServiceReservation::where('service_id', $service->id)->where('status', 1)->paginate($perPage);

        $reservationsArray = $requests->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'count'=>ServiceReservation::where('service_id', $service->id)->where('status', 1)->count(),
            'reservations' => UserSingleServiceReservationResource::collection($requests),
            'pagination' => $pagination
        ];
    }
}
