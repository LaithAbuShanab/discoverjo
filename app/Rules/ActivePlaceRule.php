<?php

namespace App\Rules;

use App\Models\Place;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActivePlaceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $place = Place::where('slug', $value)->first();
        if(!$place) return;
        if (!$place->status) {
            $fail(__('validation.api.the-selected-place-is-not-active'));
        }
    }
}
