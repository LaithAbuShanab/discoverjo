<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfServiceActiveRuel implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = Service::findBySlug($value);
        if (!$service) return;
        if($service->status != 1) {
            $fail(__('this-service-not-longer-active'));
        };
    }
}
