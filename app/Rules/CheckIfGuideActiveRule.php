<?php

namespace App\Rules;

use App\Models\GuideTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfGuideActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $guideTrip = GuideTrip::findBySlug($value);
        if (!$guideTrip) return;
        $guideStatus = $guideTrip->guide?->status;
        if (!$guideStatus) {
            $fail(__('validation.api.the-guide-not-longer-active'));
        }
    }
}
