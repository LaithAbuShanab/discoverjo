<?php

namespace App\Rules;

use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckIfGuideTripActiveOrInFuture implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $activeTrip = GuideTrip::findBySlug($value);
        if(!$activeTrip) return;
        if ($activeTrip->status != 1) {
            $fail(__('validation.api.trip_registration_closed'));
            return;
        }

        if (Carbon::parse($activeTrip->start_datetime)->isPast()) {
            $fail(__('validation.api.trip_has_started', ['start_datetime' => $activeTrip->start_datetime]));
            return;
        }
    }

}
