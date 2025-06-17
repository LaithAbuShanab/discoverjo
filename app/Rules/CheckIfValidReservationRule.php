<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class CheckIfValidReservationRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $serviceSlug = $this->data['service_slug'] ?? null;
        $date = $this->data['date'] ?? null;
        $startTime = $this->data['start_time'] ?? null;
        $reservations = $this->data['reservations'] ?? [];
        $user = Auth::guard('api')->user();

        if (!$serviceSlug || !$date || !$startTime || !is_array($reservations)) {
            return;
        }

        $requestedQty = collect($reservations)->sum(fn($r) => (int) ($r['quantity'] ?? 0));
        if ($requestedQty < 1) {
            $fail(__('validation.invalid_quantity'));
            return;
        }

        $service = Service::findBySlug($serviceSlug);
        if (!$service) return;

        $booking = $service->serviceBookings()
            ->where('available_start_date', '<=', $date)
            ->where('available_end_date', '>=', $date)
            ->first();

        if (!$booking) {
            $fail(__('validation.invalid_date_range'));
            return;
        }

        if ($service->reservations()
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->where('start_time', $startTime)
            ->exists()
        ) {
            $fail(__('validation.duplicate_exact_slot_booking', [
                'time' => $startTime,
                'date' => $date,
            ]));
            return;
        }

        $dayOfWeek = Carbon::parse($date)->format('l');
        $bookingDay = $booking->serviceBookingDays()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$bookingDay) {
            $fail(__('validation.day_not_available', ['day' => $dayOfWeek]));
            return;
        }

        $slotStart = Carbon::parse($bookingDay->opening_time);
        $slotEnd = Carbon::parse($bookingDay->closing_time);
        $requestedTime = Carbon::createFromFormat('H:i', $startTime);

        $duration = $booking->session_duration;
        $capacity = $booking->session_capacity;

        if (!$this->isValidSlot($slotStart, $slotEnd, $requestedTime, $duration)) {
            $fail(__('validation.invalid_slot_start_time', [
                'duration' => $duration,
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
            ]));
            return;
        }

        $reservedStart = $requestedTime->copy();
        $reservedEnd = $requestedTime->copy()->addMinutes($duration);

        $query = $service->reservations()
            ->where('user_id', $user->id)
            ->whereNot('status',2)
            ->where('date', $date)
            ->where(function ($query) use ($reservedStart, $reservedEnd, $duration) {
                $query->whereTime('start_time', '<', $reservedEnd->format('H:i'))
                    ->whereRaw("ADDTIME(start_time, SEC_TO_TIME(? * 60)) > ?", [
                        $duration,
                        $reservedStart->format('H:i')
                    ]);
            });
        $hasConflict= $query->exists();

        $conflictingReservation = $query->with('service')->first();

        //add the name of the service

        if ($hasConflict && $conflictingReservation) {
            $fail(__('validation.user_reservation_conflict', [
                'service' => $conflictingReservation->service->name
            ]));
            return;
        }

        // ✅ Fix: calculate total existing quantity for this slot using the reservation_details table
        $reservationIds = $service->reservations()
            ->where('date', $date)
            ->whereNot('status',2)
            ->where('start_time', $startTime)
            ->pluck('id');

        $existingQty = DB::table('service_reservation_details')
            ->whereIn('service_reservation_id', $reservationIds)
            ->sum('quantity');

        if ($capacity && ($existingQty + $requestedQty > $capacity)) {
            $fail(__('validation.slot_overbooked', [
                'capacity' => $capacity,
                'remaining' => max($capacity - $existingQty, 0),
            ]));
        }
    }


    protected function isValidSlot(Carbon $slotStart, Carbon $slotEnd, Carbon $requestedTime, int $duration): bool
    {
        $slot = $slotStart->copy();
        while ($slot->copy()->addMinutes($duration)->lte($slotEnd)) {
            if ($slot->equalTo($requestedTime)) {
                return true;
            }
            $slot->addMinutes($duration);
        }
        return false;
    }
}

