<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\UsersTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserAttendanceAndPastTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $attendance = UsersTrip::where('user_id', $userId)->where($attribute, $value)->where('status', '1')->exists();
        $currentTrip = Trip::find($value);
        if(!$currentTrip)return;
        $owner = $currentTrip->user_id;
        if (!$attendance && $owner != $userId) {
            $fail(__('validation.api.you_are_not_attendance_in_this'));
        }
        $trip_date = Trip::find($value)?->date_time;
        $now = now()->setTimezone('Asia/Riyadh');
        if ($trip_date > $now) {
            $fail(__('validation.api.you_cant_make_review_for_upcoming_trip'));
        }
    }
}
