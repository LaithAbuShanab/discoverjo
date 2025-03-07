<?php

namespace App\Rules;

use App\Models\GuideTrip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfGuideTripReflectRule implements ValidationRule
{
    protected $start_datetime;
    protected $end_datetime;

    public function __construct($start_datetime, $end_datetime)
    {
        $this->start_datetime = $start_datetime;
        $this->end_datetime = $end_datetime;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;

        if(isset(request()->guide_trip_id) && !empty(request()->guide_trip_id)){
            $userTrips = GuideTrip::where('id', '!=', request()->guide_trip_id)->where('guide_id', $userId)->where('status', 1)->get();
        }else{
            $userTrips = GuideTrip::where('guide_id', $userId)->where('status', 1)->get();

        }

        foreach ($userTrips as $userTrip) {
            if (
                ($userTrip->start_datetime < $this->end_datetime && $userTrip->end_datetime > $this->start_datetime) ||
                ($userTrip->start_datetime < $this->start_datetime && $userTrip->end_datetime > $this->start_datetime) ||
                ($userTrip->start_datetime < $this->end_datetime && $userTrip->end_datetime > $this->end_datetime)
            ) {
                $fail(__('validation.api.trip_conflict'));
            }
        }
    }
}
