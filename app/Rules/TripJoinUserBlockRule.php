<?php

namespace App\Rules;

use App\Models\Trip;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class TripJoinUserBlockRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trip = Trip::findBySlug($value);
        $tripOwner = $trip->user;
        $currentUser = Auth::guard('api')->user();

        if ($currentUser->hasBlocked($tripOwner) || $tripOwner->hasBlocked($currentUser)) {
            $fail(__('validation.api.generic-action-denied'));
        }
    }
}
