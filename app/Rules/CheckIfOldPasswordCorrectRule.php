<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CheckIfOldPasswordCorrectRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();

        // Check if the provided old password matches the user's current password
        if (!$user || !Hash::check($value, $user->password)) {
            $fail(__('validation.api.the-provided-old-password-is-incorrect'));
        }
    }
}
