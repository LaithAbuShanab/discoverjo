<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfTypeAndSlugRule implements ValidationRule, DataAwareRule
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
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'guideTrip'];

        if (!in_array($this->data['type'], $acceptableType)) {
            return;
        }
        // Validate if the type class has the method `findBySlug` before using it
        $modelClass = 'App\Models\\' . ucfirst($this->data['type']);

        $favorableItem = $modelClass::findBySlug($value);

        if (!$favorableItem) {
            $fail(__('validation.api.review-id-does-not-exists'));
            return;
        }

        if ($this->data['type'] == 'place') {
            if ($favorableItem->status != 1) {
                $fail(__('validation.api.the-selected-place-is-not-active'));
            }
        }
    }
}
