<?php

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPeriodMonthYearExistsInPropertyRule implements ValidationRule,  DataAwareRule
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
        $slug = $this->data['property_slug'] ?? null;
        $periodType = $this->data['period_type'] ?? null; // e.g., morning, evening, overnight
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
            'morning' => 1,
            'evening' => 2,
            'overnight' => 3,
        ];

        $mappedType = $periodMap[$periodType] ?? null;

        if (! $mappedType) {
            $fail(__('Invalid period type.'));
            return;
        }

        // Check if property has this period type
        $hasPeriod = $property->periods->contains('type', $mappedType);

        if (! $hasPeriod) {
            $fail(__('This property does not support the selected period.'));
            return;
        }

        // OPTIONAL: Check if the month/year is available
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateString();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        $hasAvailability = $property->availabilities()
            ->where('type', $mappedType)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('availability_start_date', [$startDate, $endDate])
                    ->orWhereBetween('availability_end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('availability_start_date', '<=', $startDate)
                            ->where('availability_end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if (! $hasAvailability) {
            $fail(__('This period is not available for the selected month and year.'));
        }
    }
}
