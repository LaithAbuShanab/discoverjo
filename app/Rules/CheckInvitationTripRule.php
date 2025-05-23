<?php

namespace App\Rules;

use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckInvitationTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = Trip::where('slug', $value)->first();

        if (count($trip->usersTrip->where('user_id', Auth::guard('api')->user()->id)) == 0 && $trip->user_id == Auth::guard('api')->user()->id) {
            $fail(__('validation.api.you-are-owner-of-trip'));
        }

        if (count($trip->usersTrip->where('user_id', Auth::guard('api')->user()->id)) == 0 && $trip->user_id != Auth::guard('api')->user()->id) {
            $fail(__('validation.api.you-didnt-join-trip'));
        }
    }
}
