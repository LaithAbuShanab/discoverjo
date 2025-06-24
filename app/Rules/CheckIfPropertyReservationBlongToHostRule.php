<?php

namespace App\Rules;

use App\Models\PropertyReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfPropertyReservationBlongToHostRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $reservation = PropertyReservation::find($value);
        if (!$reservation) return;
        $property = $reservation->property;
        if(!$property) return;
        if($property->host_id != $user->id){
            $fail("this reservation does not belong to this host");
        };

    }
}
