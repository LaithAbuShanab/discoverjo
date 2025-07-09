<?php

namespace App\Rules;

use App\Models\ServiceReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;


class CheckIfServiceReservationInThePastByIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $serviceReservation = ServiceReservation::find($value);
        if(!$serviceReservation) return;
        // Assume 'date' is passed in from the form/request
        $date = $serviceReservation->date;
        $startTime = $serviceReservation->start_time;

        if (!$date || !$startTime) {
            $fail('the date and time not exists');
            return;
        }

        $reservationDateTime = Carbon::parse("$date $startTime");

        if ($reservationDateTime->lt(now())) {
            $fail('You cannot update a reservation that is in the past.');
        }
    }
}
