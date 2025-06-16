<?php


namespace App\Rules;

use App\Models\ServiceReservation;
use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckIfValidDateReservationUpdateRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $reservationId = (int) request()->id ?? null;
        $date = $this->data['date'] ?? null;
        $startTime = $this->data['start_time'] ?? null;
        $reservations = $this->data['reservations'] ?? [];
        $user = Auth::guard('api')->user();

        if (!$reservationId || !$date || !$startTime || !is_array($reservations)) {
            $fail(__('validation.api.something_went_wrong'));
            return;
        }

        $reservation = ServiceReservation::with('service')->find($reservationId);

        if (!$reservation || $reservation->user_id !== $user->id) {
            $fail(__('validation.api.reservation-id-invalid'));
            return;
        }

        $service = $reservation->service;

        $requestedQty = collect($reservations)->sum(fn($r) => (int) ($r['quantity'] ?? 0));
        if ($requestedQty < 1) {
            $fail(__('validation.invalid_quantity'));
            return;
        }

        $booking = $service->serviceBookings()
            ->where('available_start_date', '<=', $date)
            ->where('available_end_date', '>=', $date)
            ->first();

        if (!$booking) {
            $fail(__('validation.invalid_date_range'));
            return;
        }

        $dayOfWeek = Carbon::parse($value)->format('l');
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

        // ⛔ Prevent time conflict, excluding the current reservation ID
        $reservedStart = $requestedTime->copy();
        $reservedEnd = $requestedTime->copy()->addMinutes($duration);

        $conflictQuery = $service->reservations()
            ->where('user_id', $user->id)
            ->where('id', '<>', $reservation->id)
            ->where('date', $date)
            ->where(function ($query) use ($reservedStart, $reservedEnd, $duration) {
                $query->whereTime('start_time', '<', $reservedEnd->format('H:i'))
                    ->whereRaw("ADDTIME(start_time, SEC_TO_TIME(? * 60)) > ?", [
                        $duration,
                        $reservedStart->format('H:i')
                    ]);
            });

        if ($conflictQuery->exists()) {
            $conflicting = $conflictQuery->with('service')->first();
            $fail(__('validation.user_reservation_conflict', [
                'service' => $conflicting->service->name ?? 'another service',
            ]));
            return;
        }

        // ✅ Capacity check (excluding the current reservation’s quantity)
        $reservationIds = $service->reservations()
            ->where('date', $date)
            ->where('start_time', $startTime)
            ->where('id', '<>', $reservation->id) // exclude current
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

