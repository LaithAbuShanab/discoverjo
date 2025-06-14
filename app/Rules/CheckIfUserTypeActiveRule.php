<?php

namespace App\Rules;

use App\Models\GuideTrip;
use App\Models\Service;
use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfUserTypeActiveRule implements ValidationRule, DataAwareRule
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
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'plan', 'guideTrip','service'];
        $type = $this->data['type'];
        if (!in_array($type, $acceptableType)) {
            return;
        }

        if ($type == 'trip') {
            $trip = Trip::findBySlug($value);
            if (!$trip) return;
            $ownerStatus = $trip->user?->status;
            if (!$ownerStatus) {
                $fail(__('validation.api.the-user-owner-this-trip-not-longer-active'));
                return;
            }
        }

        if ($type == 'guideTrip') {
            $guideTrip = GuideTrip::findBySlug($value);
            if (!$guideTrip) return;
            $ownerStatus = $guideTrip->guide?->status;
            if (!$ownerStatus) {
                $fail(__('validation.api.the-user-owner-this-trip-not-longer-active'));
                return;
            }
        }

        if ($type == 'service') {
            $service = Service::findBySlug($value);
            if (!$service) return;
            $ownerStatus = $service->provider?->status;
            if (!$ownerStatus) {
                $fail(__('validation.api.the-user-owner-this-service-not-longer-active'));
                return;
            }
        }
    }
}
