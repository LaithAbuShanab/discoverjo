<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfCanMakeTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!isset(request()->trip_id)) {
            $trip = Trip::where('user_id', Auth::guard('api')->user()->id)
                ->whereDate('date_time', '=', request()->date)
                ->where('status', '1')
                ->first();

            if ($trip) {
                $fail(__('validation.api.cant-make-trip-in-the-same-date-time'));
            }

            // Verify that the user is on a trip with the same date as the trip they want to take.
            $userTrips = UsersTrip::where('user_id', Auth::guard('api')->user()->id)
                ->where('status', '1')
                ->get();

            foreach ($userTrips as $userTrip) {
                $trip = Trip::where('id', $userTrip->trip_id)
                    ->whereDate('date_time', '=', request()->date)
                    ->where('status', '1')
                    ->first();

                if ($trip) {
                    $fail(__('validation.api.cant-make-trip-in-this-date-you-already-on-trip'));
                }
            }
        }

        $date = request()->date;
        $time = request()->time;

        $date_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, 'Asia/Riyadh');
        $now = Carbon::now('Asia/Riyadh');

        if ($date_time < $now) {
            $fail(__('validation.api.time-should-not-be-in-the-past'));
        }
    }
}
