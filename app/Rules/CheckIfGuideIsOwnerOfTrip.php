<?php

namespace App\Rules;

use App\Models\GuideTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfGuideIsOwnerOfTrip implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = GuideTrip::findBySlug($value);
        if(!$trip) return;
        if ($trip->guide_id !== Auth::guard('api')->user()->id) {
            $fail(__('validation.api.not_owner_of_trip'));
        }
    }

}
