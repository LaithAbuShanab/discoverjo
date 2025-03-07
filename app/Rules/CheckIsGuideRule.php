<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIsGuideRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isUserGuide = Auth::guard('api')->user()->is_guide;
        if(!$isUserGuide){
            $fail(__('validation.api.you_should_be_guide_to_create_guide_trip'));
        }

    }
}
