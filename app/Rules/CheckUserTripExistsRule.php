<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\UsersTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckUserTripExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Trip::where('id', $value)->where('user_id', Auth::guard('api')->user()->id)->exists()) {
            $fail(__('validation.api.you-are-owner-of-trip'));
        }

        if (!UsersTrip::where('user_id', Auth::guard('api')->user()->id)->where($attribute, $value)->exists()) {
            $fail(__('validation.api.you-didnt-join-trip'));
        }

        if (UsersTrip::where('user_id', Auth::guard('api')->user()->id)->where('status', '2')->where($attribute, $value)->first()) {
            $fail(__('validation.api.trip-already-canceled'));
        }
    }
}
