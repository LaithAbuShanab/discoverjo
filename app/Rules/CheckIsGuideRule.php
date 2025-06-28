<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIsGuideRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find(Auth::guard('api')->user()->id);

        if (!$user) return;

        if (! $user->userTypes()->where('type', 2)->exists()) {
            $fail(__('validation.api.the-provided-id-not-guide'));
            return;
        }

        if ($user->status != 1) {
            $fail(__('validation.api.the-guide-not-active'));
        }
    }
}
