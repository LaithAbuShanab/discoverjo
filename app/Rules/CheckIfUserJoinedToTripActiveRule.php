<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfUserJoinedToTripActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userGideTrip = GuideTripUser::find($value);
        if(!$userGideTrip) return;
        if(!$userGideTrip->user?->status){
            $fail(__('validation.api.the-user-not-longer-active'));
        }

    }
}
