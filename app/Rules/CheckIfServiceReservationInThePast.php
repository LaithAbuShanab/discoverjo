<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfServiceReservationInThePast implements ValidationRule, DataAwareRule
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
        $startTime = $this->data['start_time'];
        $date= $value;
        $reservationDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date $startTime");

        if ($reservationDateTime->lt(now())) {
            $fail('this reservation in the past so you can not to update it.');
        }
    }
}
