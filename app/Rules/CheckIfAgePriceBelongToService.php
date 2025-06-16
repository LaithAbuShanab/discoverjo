<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfAgePriceBelongToService implements ValidationRule, DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $serviceSlug = $this->data['service_slug'] ?? null;

        if (!$serviceSlug || !$value) {
            return; // Let other rules handle missing data
        }

        $service = Service::with('priceAges')->where('slug', $serviceSlug)->first();

        if (!$service) {
            $fail(__('validation.invalid_service_slug'));
            return;
        }

        $exists = $service->priceAges->contains('id', $value);

        if (!$exists) {
            $fail(__('validation.invalid_price_age', ['id' => $value]));
        }
    }
}
