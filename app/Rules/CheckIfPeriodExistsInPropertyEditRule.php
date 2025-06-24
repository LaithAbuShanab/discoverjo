<?php

namespace App\Rules;

use App\Models\Property;
use App\Models\PropertyReservation;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPeriodExistsInPropertyEditRule implements ValidationRule,  DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $propertyReservation = PropertyReservation::find($this->data['reservation_id']);
        if(!$propertyReservation)return;
        $property = $propertyReservation->property;
        if(!$property)return;

        $types = [
            'morning' => 1,
            'evening' => 2,
            'day'     => 3,
        ];

        // Normalize input
        $periodType = strtolower($value);

        if (!isset($types[$periodType])) {
            $fail(__("Invalid period type."));
        }

        $type = $types[$periodType];
        if (! $property->periods()->where('type', $type)->exists()) {
            $fail("The selected period type is not available for this property.");
        }
    }
}
