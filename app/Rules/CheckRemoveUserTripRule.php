<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckRemoveUserTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip_id = Trip::where('slug', $value)->first()->id;
        $user_id = User::where('slug', request('user_slug'))->first()->id;

        if (!UsersTrip::where('trip_id', $trip_id)->where('user_id', $user_id)->exists()) {
            $fail(__('validation.api.the-user-is-not-a-member-of-this-trip'));
        }
    }
}
