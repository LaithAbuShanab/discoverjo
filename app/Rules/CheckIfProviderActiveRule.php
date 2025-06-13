<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfProviderActiveRule implements ValidationRule
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
        $providerStatus = $service->provider?->status;
        if (!$providerStatus) {
            $fail(__('validation.api.the-provider-not-longer-active'));
        }
    }
}
