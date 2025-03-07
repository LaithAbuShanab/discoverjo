<?php

namespace App\Rules;

use App\Models\RatingGuide;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserMakeRatingOnGuideRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        if(RatingGuide::where('user_id',$userId)->where('guide_id',$value)->exists()){
            $fail(__('validation.api.you-made-rating-already'));
        }

    }
}
