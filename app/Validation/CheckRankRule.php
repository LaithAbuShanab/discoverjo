<?php

namespace App\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;


class CheckRankRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (request()->input('place_type') === 'top_ten' && empty($value)) {
            $fail(__('validation.required', ['attribute' => $attribute]));
        }
    }
}
