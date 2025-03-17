<?php

namespace App\Rules;

use App\Models\GuideTrip;
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
        $guideTrip = GuideTrip::findBySlug($value);
        if(!$guideTrip) return;
        $userInTrip = GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id', Auth::guard('api')->user()->id)->exists();
        if(!$userInTrip) return;
        if ($userInTrip) {
            $fail(__('validation.api.already_in_trip'));
        }

    }
}
