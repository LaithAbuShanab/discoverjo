<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OpeningHoursRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $daysOfWeek = request()->day_of_week;
        if ($daysOfWeek) {
            foreach ($daysOfWeek as $index => $days) {
                if (empty($value[$index])) {
                    $fail(__('validation.api.' . $attribute . '-is-required'));
                    return;
                }
            }
        }
    }



}
