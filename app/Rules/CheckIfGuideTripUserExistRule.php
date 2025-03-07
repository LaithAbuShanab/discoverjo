<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfGuideTripUserExistRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userInTrip = GuideTripUser::where('guide_trip_id', $value)->where('user_id', Auth::guard('api')->user()->id)->exists();
        if ($userInTrip) {
            $fail(__('validation.api.already_in_trip'));
        }

    }
}
