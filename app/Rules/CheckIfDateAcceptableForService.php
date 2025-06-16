<?php

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;


class CheckIfDateAcceptableForService implements ValidationRule, DataAwareRule
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
        $serviceSlug = $this->data['service_slug'];
        $service = Service::findBySlug($serviceSlug);
        if (!$service) return;
        $serviceDate = $service->serviceBookings?->first();
        if (!$serviceDate) return;

        $dateValue = Carbon::createFromFormat('Y-m-d', $value);
        $start = Carbon::parse($serviceDate->available_start_date);
        $end = Carbon::parse($serviceDate->available_end_date);


        if ($dateValue->lt($start) || $dateValue->gt($end)) {
            $fail(__('validation.invalid_date_range', [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ]));
        }

        $dayOfWeek = $dateValue->format('l'); // e.g., 'Monday', 'Tuesday'

        // Check if this day is allowed for booking
        $isAvailableOnDay = $serviceDate->serviceBookingDays()
            ->where('day_of_week', $dayOfWeek)
            ->exists();

        if (!$isAvailableOnDay) {
            $fail(__('validation.day_not_available', [
                'day' => $dayOfWeek,
            ]));
        }

    }
}
