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
            $fail(__("validation.api.invalid_period_type"));
        }

        $type = $types[$periodType];
        $property = Property::findBySlug($this->data['property_slug']);
        if (!$property) return;
        if (! $property->periods()->where('type', $type)->exists()) {
            $fail("validation.api.this_period_type_is_not_available_for_this_property");
        }
    }
}
