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
        $periodType = $this->data['period_type'] ?? null;
        $month = $this->data['month'] ?? null;
        $year = $this->data['year'] ?? null;

        if (! $slug || ! $periodType || ! $month || ! $year) {
            $fail(__('Some required data is missing.'));
            return;
        }

        $property = Property::where('slug', $slug)->with('periods')->first();

        if (! $property) {
            $fail(__('Property not found.'));
            return;
        }

        $periodMap = [
            'morning'   => 1,
            'evening'   => 2,
            'overnight' => 3,
        ];

        $mappedType = $periodMap[$periodType] ?? null;

        if (! $mappedType) {
            $fail(__('Invalid period type.'));
            return;
        }

        if (! $property->periods->contains('type', $mappedType)) {
            $fail(__('This property does not support the selected period.'));
            return;
        }

        $monthName = strtolower($month);
        $monthMap = [
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12,
        ];

        if (!isset($monthMap[$monthName])) {
            $fail(__('Invalid month name.'));
            return;
        }

        $month = $monthMap[$monthName]; // Now it's an integer
        $year = (int) $year;

        // Build date range
        try {
            $startDate = now()->setDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = now()->setDate($year, $month, 1)->endOfMonth()->toDateString();
        } catch (\Exception $e) {
            $fail(__('Invalid month or year.'));
            return;
        }

        // Check availability
        $hasAvailability = $property->availabilities()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('availability_start_date', [$startDate, $endDate])
                    ->orWhereBetween('availability_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('availability_start_date', '<=', $startDate)
                            ->where('availability_end_date', '>=', $endDate);
                    });
            })
            ->whereHas('availabilityDays.period', fn($q) => $q->where('type', $mappedType))
            ->exists();

        if (! $hasAvailability) {
            $fail(__('This period is not available for the selected month and year.'));
        }
    }
}
