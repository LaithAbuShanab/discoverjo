<?php

namespace App\Rules;

use App\Http\Controllers\Api\User\PropertyReservationApiController;
use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPropertyActiveRule implements ValidationRule
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
        if ($property->status != 1){
            $fail(__('this-property-is-inactive'));
        }
    }
}
