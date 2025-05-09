<?php

namespace App\Rules;

use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class CheckIfGuideTripActiveOrInFutureForSubscriptionRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $guideTripUser = GuideTripUser::find($value);
        if (!$guideTripUser) return;

        $activeTrip = GuideTrip::find($guideTripUser->guide_trip_id);

        if (!$activeTrip) return;
        $guideStatus = $activeTrip->guide?->status;
        if (!$guideStatus) {
            $fail(__('validation.api.the-guide-not-longer-active'));
        }

        if (!$activeTrip) return;
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
