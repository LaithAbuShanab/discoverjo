<?php

namespace App\Rules;

use App\Models\Place;
use App\Models\VisitedPlace;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfNotExistsInVistedPlaceTableRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $place = Place::findBySlug($value);
        if (!$place) {
            return;
        }
        if (!VisitedPlace::where('user_id', $userId)->where('place_id', $place->id)->exists()) {
            $fail(__('validation.api.this_place_not_in_your_visited_place_list'));
        }
    }
}
