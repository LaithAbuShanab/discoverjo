<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class OtherUserProfileBlockedRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::findBySlug($value);
        $currentUser = Auth::guard('api')->user();

        if ($user->hasBlocked($currentUser)) {
            $fail(__('validation.api.generic-action-denied'));
        }
    }
}
