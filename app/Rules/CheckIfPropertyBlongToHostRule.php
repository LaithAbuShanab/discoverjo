<?php

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfPropertyBlongToHostRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $property = Property::findBySlug($value);
        if (!$property) return;
        if ($property->host_id != $user->id) {
            $fail('validation.api.this-property-not-belong-to-host');
        }
    }
}
