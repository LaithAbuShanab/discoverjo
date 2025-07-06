<?php

namespace App\Rules;

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPeriodMonthYearExistsInPropertyRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = $this->data['property_slug'] ?? null;
        $month = $this->data['month'] ?? null;
        $year = $this->data['year'] ?? null;

        if (!$slug || !$month || !$year) {
            $fail(__('validation.api.missing_parameters'));
            return;
        }

        $property = Property::where('slug', $slug)->first();

        if (!$property) {
            $fail(__('validation.api.property_not_found'));
            return;
        }

        $monthName = strtolower($month);
        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3,
            'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9,
            'october' => 10, 'november' => 11, 'december' => 12,
        ];

        if (!isset($monthMap[$monthName])) {
            $fail(__('validation.api.invalid_month'));
            return;
        }

        $month = $monthMap[$monthName];
        $year = (int) $year;

        try {
            $startDate = now()->setDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = now()->setDate($year, $month, 1)->endOfMonth()->toDateString();
        } catch (\Exception $e) {
            $fail(__('validation.api.invalid_year'));
            return;
        }

        // Check if the property has ANY availability in the given month/year
        $hasAvailability = $property->availabilities()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('availability_start_date', [$startDate, $endDate])
                    ->orWhereBetween('availability_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('availability_start_date', '<=', $startDate)
                            ->where('availability_end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if (!$hasAvailability) {
            $fail(__('validation.api.this_month_has_no_availability_for_this_property'));
        }
    }
}
