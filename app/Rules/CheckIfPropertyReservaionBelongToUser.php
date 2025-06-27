<?php

namespace App\Rules;

use App\Models\PropertyReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfPropertyReservaionBelongToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $reservation = PropertyReservation::where('id',$value)->where('user_id',$user->id)->exists();
        if (!$reservation) {
            $fail(__('validation.api.this-reservation-not-belong-to-user'));
        };
    }
}
