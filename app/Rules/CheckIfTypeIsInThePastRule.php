<?php

namespace App\Rules;

use App\Models\PropertyReservation;
use App\Models\ServiceReservation;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfTypeIsInThePastRule implements ValidationRule, DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $now = now()->setTimezone('Asia/Riyadh');
        $type = $this->data['type'] ?? null;

        $acceptableTypes = ['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'];
        if (!in_array($type, $acceptableTypes)) {
            return;
        }

        $modelClass = 'App\Models\\' . ucfirst($type);
        $reviewableItem = $modelClass::findBySlug($value);

        if (!$reviewableItem) {
            $fail(__('validation.api.review-id-does-not-exists'));
            return;
        }

        switch ($type) {
            case 'place':
                if ($reviewableItem->status != 1) {
                    $fail(__('validation.api.the-selected-place-is-not-active'));
                }
                break;

            case 'trip':
                $userId = Auth::guard('api')->id();
                $isParticipant = UsersTrip::where('user_id', $userId)
                    ->where('trip_id', $reviewableItem->id)
                    ->where('status', '1')
                    ->exists();

                if (!$isParticipant && $reviewableItem->user_id !== $userId) {
                    $fail(__('validation.api.you_are_not_attendance_in_this'));
                    return;
                }

                if (in_array($reviewableItem->status, [2, 3])) {
                    $fail(__('validation.api.this-trip-was-deleted'));
                    return;
                }

                if ($reviewableItem->date_time > $now) {
                    $fail(__('validation.api.you_cant_make_review_for_upcoming_trip'));
                    return;
                }
                break;

            case 'service':
                $authUserId = Auth::guard('api')->id();
                $reservation = ServiceReservation::where('service_id', $reviewableItem->id)
                    ->where('user_id', $authUserId)
                    ->first();

                if (!$reservation) {
                    $fail(__('validation.api.you_have_not_reservation_for_this_service'));
                    return;
                }

                $reservationDateTime = Carbon::parse("{$reservation->date} {$reservation->start_time}", 'Asia/Riyadh');
                if ($reservationDateTime->gt($now)) {
                    $fail(__('validation.api.you_cannot_make_review_for_upcoming_service'));
                    return;
                }
                break;

            case 'property':
                $authUserId = Auth::guard('api')->id();
                $reservation = PropertyReservation::where('property_id', $reviewableItem->id)
                    ->where('user_id', $authUserId)
                    ->where('status', 3)
                    ->orderByDesc('check_out')
                    ->first();

                if (!$reservation) {
                    $fail(__('validation.api.you_have_not_reservation_for_this_property'));
                    return;
                }

                $checkOut = Carbon::parse($reservation->check_out)->setTimezone('Asia/Riyadh');
                if ($checkOut->gt($now)) {
                    $fail(__('validation.api.you_cannot_make_review_for_upcoming_property'));
                    return;
                }
                break;

            default: // For event, volunteering, guideTrip
                $startDate = $reviewableItem->start_datetime ?? null;
                if ($startDate && Carbon::parse($startDate)->gt($now)) {
                    $fail(__('validation.api.you_cannot_make_review_for_upcoming_event'));
                }
                break;
        }
    }
}
