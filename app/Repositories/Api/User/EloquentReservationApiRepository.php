<?php

namespace App\Repositories\Api\User;


use App\Http\Resources\AllServicesResource;
use App\Http\Resources\GroupedReservationResource;
use App\Http\Resources\UserSingleServiceReservationResource;
use App\Interfaces\Gateways\Api\User\ReservationApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ServiceApiRepositoryInterface;
use App\Models\Service;
use App\Models\ServiceReservation;
use App\Models\ServiceReservationDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


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

        $slots = [];

        while ($startTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $startTime->format('H:i');
            $slotEnd = $startTime->copy()->addMinutes($duration)->format('H:i');

            // Sum quantity from reservation details via reservations on this date and slot
            $reservedQuantity = ServiceReservationDetail::whereHas('reservation', function ($query) use ($service, $date, $slotStart) {
                $query->where('service_id', $service->id)
                    ->where('date', $date)
                    ->where('start_time', $slotStart);
//                    ->where('status', 1); // Optional: only confirmed reservations
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

        return $reservation;
    }


    public function UserServiceReservations($data)
    {
        $service  = Service::findBySlug($data['service_slug']);
        $user = Auth::guard('api')->user();
        $reservations = ServiceReservation::where('service_id', $service->id)->where('user_id',$user->id)->get();
        return UserSingleServiceReservationResource::collection($reservations);
    }

    public function allReservations()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $user = Auth::guard('api')->user();
        $reservations = ServiceReservation::where('user_id',$user->id)->paginate($perPage);
        $reservationsArray = $reservations->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
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
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'contact_info' => $data['contact_info'],
            'total_price' => $totalPrice,
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
        $statusMap = [
            'confirmed' => 1,
            'cancelled' => 2,
        ];
        $reservationId = $data['id'];
        $statusLabel = $data['status'];
        $reservation = ServiceReservation::findOrFail($reservationId);
        $reservation->status = $statusMap[$statusLabel];
        $reservation->save();
        return $reservation;
    }

    public function providerRequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $service = Service::findBySlug($slug);
        $requests = ServiceReservation::where('service_id', $service->id)->where('status',0)->paginate($perPage);

        $reservationsArray = $requests->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'reservations' => UserSingleServiceReservationResource::collection($requests),
            'pagination' => $pagination
        ];
    }

    public function approvedRequestReservations($slug)
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $service = Service::findBySlug($slug);
        $requests = ServiceReservation::where('service_id', $service->id)->where('status',1)->paginate($perPage);

        $reservationsArray = $requests->toArray();

        $pagination = [
            'next_page_url' => $reservationsArray['next_page_url'],
            'prev_page_url' => $reservationsArray['next_page_url'],
            'total' => $reservationsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'reservations' => UserSingleServiceReservationResource::collection($requests),
            'pagination' => $pagination
        ];
    }

}
