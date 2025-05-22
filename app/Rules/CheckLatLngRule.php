<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckLatLngRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }
        // Apply regex to the **original string**, not the float
        if (!preg_match('/^-?\d{1,3}(\.\d{1,8})?$/', trim($value))) {
            $fail("The {$attribute} must have up to 6 decimal places and max 3 digits before the dot.");
            return;
        }
    }
}
