<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\UsersTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckUserRequestTripExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->id();
        $trip = Trip::where('slug', $value)->first();

        if (!$trip) return;

        // Case 1: If User is Owner
        if ($trip->user_id === $userId) {
            $fail(__('validation.api.you-are-owner-of-trip'));
            return;
        }

        // Fetch user trip status in a single query
        $userTrip = UsersTrip::where('user_id', $userId)
            ->where('trip_id', $trip->id)
            ->whereIn('status', [0, 2, 3])
            ->first();

        // Case 2: If User Is Not a Member
        if (!$userTrip) {
            $fail(__('validation.api.you-didnt-join-trip'));
            return;
        }

        // Case 3 & 4: If User Has Been Canceled or Left
        $statusMessages = [
            2 => __('validation.api.trip-already-canceled'),
            3 => __('validation.api.you-left-trip'),
        ];

        if ($userTrip)
            if (isset($statusMessages[$userTrip->status])) {
                $fail($statusMessages[$userTrip->status]);
            }
    }
}
