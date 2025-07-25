<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CurrentBlockUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $guide = User::findBySlug($value);

        $hasBlocked = Auth::guard('api')->user()->hasBlocked($guide);

        if ($hasBlocked) {
            $fail(__('validation.api.generic-action-denied'));
        }
    }
}
