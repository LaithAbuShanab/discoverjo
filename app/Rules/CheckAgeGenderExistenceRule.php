<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckAgeGenderExistenceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip_id = Trip::where('slug', request()->trip_slug)->first()->id;
        $trip = Trip::find($trip_id);

        if (!$trip) return;

        if ($trip) {
            $user = Auth::guard('api')->user();
            $birthday = $user->birthday;

            if (!$birthday) {
                $fail(__('validation.api.you-should-enter-your-birthday-first'));
            }

            $birthday = new \DateTime($birthday);
            $currentDate = new \DateTime();
            $currentDate->setTimezone(new \DateTimeZone('Asia/Riyadh'));
            $age = abs($currentDate->diff($birthday)->y);
            $tripDateTime = new \DateTime($trip->date_time);

            if ($trip->attendance_number == UsersTrip::where('trip_id', $trip_id)->where('status', '1')->count()) {
                $fail(__('validation.api.this-trip-has-exceeded-the-required-number'));
            }

            if ($currentDate->format('Y-m-d H:i:s') > $tripDateTime->format('Y-m-d H:i:s')) {
                $fail(__('validation.api.this-journey-has-already-moved-on'));
            }

            if (!(json_decode($trip->age_range)->min <= $age && json_decode($trip->age_range)->max >= $age) && ($trip->sex == $user->sex || $trip->sex == 0)) {
                $fail(__('validation.api.age-or-sex-not-acceptable'));
            }

            if (UsersTrip::where('trip_id', $trip_id)->where('user_id', Auth::guard('api')->user()->id)->where('status', '2')->exists()) {
                $fail(__('validation.api.join-request-cancelled-by-owner'));
            }

            if (UsersTrip::where('trip_id', $trip_id)->where('user_id', Auth::guard('api')->user()->id)->whereIn('status', ['0', '1'])->exists()) {
                $fail(__('validation.api.already-joined-this-trip'));
            }

            if (Trip::where('user_id', Auth::guard('api')->user()->id)->where('id', $trip_id)->exists()) {
                $fail(__('validation.api.creator-cannot-join-trip'));
            }

            if (!Trip::whereIn('sex', [$user->sex, 0])->where('id', $trip_id)->exists()) {
                $fail(__('validation.api.you-are-not-allowed-to-join-this-trip'));
            }

            $hasJoinTrip = UsersTrip::where('user_id', Auth::guard('api')->user()->id)->where('status', '1')->first();
            if ($hasJoinTrip) {
                $dateJoinTrip = Carbon::parse($hasJoinTrip->trip->date_time)->format('Y-m-d');
                $dateTrip =  Carbon::parse($trip->date_time)->format('Y-m-d');
                if ($dateJoinTrip == $dateTrip) {
                    $fail(__('validation.api.already-joined-another-trip-on-same-date'));
                }
            }
        }
    }
}
