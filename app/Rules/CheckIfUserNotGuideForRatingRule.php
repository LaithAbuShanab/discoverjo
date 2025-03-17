<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserNotGuideForRatingRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user= User::findBySlug($value);
        if(!$user) return;
        if(Auth::guard('api')->user()->id == $user->id){
            $fail(__('validation.api.you-can-not-make-rating-for-yourself'));
            return;
        }
    }
}
