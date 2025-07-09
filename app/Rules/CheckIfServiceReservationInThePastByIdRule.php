<?php

namespace App\Rules;

use App\Models\ServiceReservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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

        $reservationDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date $startTime");

        if ($reservationDateTime->lt(now())) {
            $fail('you can not update reservation in the past');
        }
    }
}
