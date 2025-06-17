<?php

namespace App\Rules;

use App\Models\ServiceReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfReservationBelongToProvider implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $reservation = ServiceReservation::find($value);
        if (!$reservation) return;

        $providerId = $reservation?->service?->provider?->id;
        if ($providerId != $user->id) {
            $fail(__('validation.api.this-service-not-belong-to-you'));
        }

        if ($reservation->user->status != 1) {
            $fail(__('validation.api.this-user-not-longer-active'));
        }
    }
}
