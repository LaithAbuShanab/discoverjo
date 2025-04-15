<?php

namespace App\Rules;

use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfTheUserOwnTheTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $guideTripUser = GuideTripUser::find($value);
        if (!$guideTripUser) return;
        if ($guideTripUser) {
            $guideTrip = GuideTrip::find($guideTripUser->guide_trip_id);
            if (!$guideTrip) return;
            if ($guideTrip) {
                if ($guideTrip->guide_id !== Auth::guard('api')->user()->id) {
                    $fail(__('validation.api.not_owner_of_trip'));
                }
            }
        }
    }
}
