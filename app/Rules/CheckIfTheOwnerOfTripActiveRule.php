<?php

namespace App\Rules;

use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfTheOwnerOfTripActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = Trip::findBySlug($value);
        if(!$trip) return ;
        $owner = $trip->user;
        if(!$owner) return;
        if(!$owner->status){
            $fail(__('validation.api.the-owner-of-the-trip-not-longer-active'));
        }
    }
}
