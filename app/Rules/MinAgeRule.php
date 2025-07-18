<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinAgeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $now = now()->setTimezone('Asia/Riyadh');
        $minAge = $now->subYears(13);
        if (strtotime($value) > strtotime($minAge)) {
            if ($attribute == 'data.birthday') {
                $attribute = __('validation.attributes.birthday');
            }
            $fail(__('validation.api.the-attribute-must-be-at-least-7-years-ago', ['attribute' => $attribute]));
        }
    }
}
