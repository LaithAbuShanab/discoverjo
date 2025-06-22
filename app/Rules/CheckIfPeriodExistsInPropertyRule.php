<?php

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPeriodExistsInPropertyRule implements ValidationRule,  DataAwareRule
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
        $property = Property::findBySlug($this->data['property_slug']);
        if(!$property) return;
        if (! $property->periods()->where('type', $type)->exists()) {
             $fail("The selected period type is not available for this property.");
        }
    }
}
