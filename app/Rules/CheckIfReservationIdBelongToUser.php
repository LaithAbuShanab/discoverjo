<?php

namespace App\Rules;

use App\Models\ServiceReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfReservationIdBelongToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $reservationId = $value;
        $reservation = ServiceReservation::where("id", $reservationId)->where('user_id', $user->id)->exists();
        if (!$reservation) {
            $fail(__('validation.reservation-not-belong-to-current-user'));
        }
    }
}
