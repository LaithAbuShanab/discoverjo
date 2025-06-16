<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfServiceBelongToProvider implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::guard('api')->user();
        $service = Service::findBySlug($value);
        if (!$service) return;
        if($service->provider_id != $user->id){
            $fail(__('validation.api.this-service-is-not-belong-to-you'));
        }
    }
}
