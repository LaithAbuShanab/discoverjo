<?php

namespace App\Rules;

use App\Models\ServiceReservation;
use App\Models\Trip;
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
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $now = now()->setTimezone('Asia/Riyadh');
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service'];

        if (!in_array($this->data['type'], $acceptableType)) {
            return;
        }
        // Validate if the type class has the method `findBySlug` before using it
        $modelClass = 'App\Models\\' . ucfirst($this->data['type']);

        $reviewableItem = $modelClass::findBySlug($value);

        if (!$reviewableItem) {
            $fail(__('validation.api.review-id-does-not-exists'));
            return;
        }

        if ($this->data['type'] == 'place') {
            if ($reviewableItem->status != 1) {
                $fail(__('validation.api.the-selected-place-is-not-active'));
                return;
            }
        } elseif ($this->data['type'] == 'trip') {
            $userId = Auth::guard('api')->user()->id;
            $attendance = UsersTrip::where('user_id', $userId)->where('trip_id', $reviewableItem?->id)->where('status', '1')->exists();
            $owner = $reviewableItem?->user_id;
            if (!$attendance && $owner != $userId) {
                $fail(__('validation.api.you_are_not_attendance_in_this'));
                return;
            }
            $trip = Trip::findBySlug($value);
            if ($trip->status == 2 || $trip->status == 3) {
                $fail(__('validation.api.this-trip-was-deleted'));
                return;
            }
            $now = now()->setTimezone('Asia/Riyadh');
            if ($trip?->date_time > $now) {
                $fail(__('validation.api.you_cant_make_review_for_upcoming_trip'));
                return;
            }
        } elseif ($this->data['type'] === 'service') {
            $service = $modelClass::findBySlug($value);

            $authUserId = Auth::guard('api')->id();

            $reservation = ServiceReservation::where('service_id', $service->id)
                ->where('user_id', $authUserId)
                ->first();

            if (!$reservation) {
                $fail(__('validation.api.you_have_not_reservation_for_this_service'));
                return;
            }

            $now = now()->setTimezone('Asia/Riyadh');

            $reservationDateTime = Carbon::parse("{$reservation->date} {$reservation->start_time}", 'Asia/Riyadh');

            if ($reservationDateTime->greaterThan($now)) {
                $fail(__('validation.api.you_cannot_make_review_for_upcoming_service'));
                return;
            }
        } else {
            $date = $modelClass::findBySlug($value)?->start_datetime;

            if ($date > $now) {
                $fail(__('validation.api.you_cannot_make_review_for_upcoming_event'));
                return;
            }
        }
    }
}
