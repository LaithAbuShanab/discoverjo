<?php

namespace App\Rules;

use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfCanUpdateTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = Trip::where('slug', $value)->first();
        if ($trip) {
            if ($trip->user_id !== Auth::guard('api')->user()->id) {
                $fail(__('validation.api.check-update-message-trip'));
            }
        }
    }
}
