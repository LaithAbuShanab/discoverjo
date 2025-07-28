<?php

namespace App\Rules;

use App\Models\Trip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Closure;

class TripFavUserBlockRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (request()->type == 'trip') {
            $trip = Trip::where('slug', $value)->first();
            $currentUser = Auth::guard('api')->user();
            $tripOwner = $trip->user;

            if ($currentUser && ($currentUser->hasBlocked($tripOwner) || $tripOwner->hasBlocked($currentUser))) {
                $fail(__('validation.api.generic-action-denied'));
            }
        }
    }
}
