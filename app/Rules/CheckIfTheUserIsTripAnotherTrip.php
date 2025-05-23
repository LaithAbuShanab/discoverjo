<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfTheUserIsTripAnotherTrip implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mainTripDate = Trip::where('slug', request()->trip_slug)->value('date_time');
        $mainTripDate = Carbon::parse($mainTripDate)->toDateString();

        $userId = User::where('slug', $value)->value('id');

        $conflictingTrips = UsersTrip::where('user_id', $userId)
            ->where('status', '1')
            ->whereHas('trip', function ($query) use ($mainTripDate) {
                $query->where('status', '1')
                    ->whereDate('date_time', $mainTripDate);
            })->exists();

        $conflictingTripsIds = UsersTrip::where('user_id', $userId)
            ->where('status', '1')
            ->whereHas('trip', function ($query) use ($mainTripDate) {
                $query->where('status', '1')
                    ->whereDate('date_time', $mainTripDate);
            })->pluck('trip_id')->toArray();

        if ($conflictingTrips && request()->status == "accept" && !in_array(Trip::where('slug', request()->trip_slug)->value('id'), $conflictingTripsIds)) {
            $fail(__('validation.api.this_user_has_joined_a_trip_on_the_same_date_as_your_trip'));
        }
    }
}
