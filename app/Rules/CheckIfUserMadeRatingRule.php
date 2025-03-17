<?php

namespace App\Rules;

use App\Models\RatingGuide;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserMadeRatingRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $guide = User::findBySlug($value);
        if(!$guide) return;
        if (!RatingGuide::where('guide_id',$guide->id)->where('user_id',$userId)->exists()){
            $fail(__('validation.api.you_did_not_make_rating_for_this_guide'));
        }
    }
}
