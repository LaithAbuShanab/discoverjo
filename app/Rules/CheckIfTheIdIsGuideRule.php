<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfTheIdIsGuideRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::findBySlug($value);
        if(!$user) return;
        if(!$user->is_guide){
            $fail(__('validation.api.the-provided-id-not-guide'));
        }
        if( $user->status != 1){
            $fail(__('validation.api.the-guide-not-active'));
        }
    }
}
