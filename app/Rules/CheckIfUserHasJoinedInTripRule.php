<?php

namespace App\Rules;

use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckIfUserHasJoinedInTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $activeTrip = GuideTrip::findBySlug($value);
        if(!$activeTrip) return;

        // Check if the user is part of the trip
        $userInTrip = GuideTripUser::where('guide_trip_id', $activeTrip->id)->where('user_id', $userId)->exists();
        if (!$userInTrip) {
            $fail(__('validation.api.you_should_join_trip_first'));
            return; // Exit after failing
        }

        // Check if the trip is active
        if (!$activeTrip) {
            $fail(__('validation.api.trip_not_found'));
            return; // Exit if the trip doesn't exist
        }

        if ($activeTrip->status != 1) {
            $fail(__('validation.api.this_trip_inactive'));
            return; // Exit after failing
        }

        // Check if the trip has started
        if (Carbon::parse($activeTrip->start_datetime)->isPast()) {
            $fail(__('validation.api.cannot_update_trip_started_at', ['date' => $activeTrip->start_datetime]));
        }
    }

}
