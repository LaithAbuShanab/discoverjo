<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class TripRequestUserBlockRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $targetUser = User::findBySlug($value);
        $currentUser = Auth::guard('api')->user();

        if (
            request()->status === 'accept' &&
            (
                $targetUser->hasBlocked($currentUser) ||
                $currentUser->hasBlocked($targetUser)
            )
        ) {
            $fail(__('validation.api.generic-action-denied'));
        }
    }
}
