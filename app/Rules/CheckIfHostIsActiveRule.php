<?php

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfHostIsActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $property = Property::findBySlug($value);
        if (!$property) return;
        $hostStatus = $property->host?->status;
        if (!$hostStatus) {
            $fail(__('validation.api.the-host-not-longer-active'));
        }
    }
}
