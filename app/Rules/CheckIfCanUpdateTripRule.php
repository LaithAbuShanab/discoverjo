<?php

namespace App\Rules;

use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfCanUpdateTripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = Trip::find(request()->trip_id);
        if ($trip) {
            if ($trip->usersTrip?->where('status', 1)->count() >= 1) {
                $fail(__('validation.api.check-update-message-trip'));
            }
        }
    }

}
