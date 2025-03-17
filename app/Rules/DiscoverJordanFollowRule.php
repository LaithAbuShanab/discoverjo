<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DiscoverJordanFollowRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $followingUser = User::findBySlug($value);
        if(!$followingUser)return;
        if($followingUser->id == 1){
            $fail(__('validation.api.you_can_not_unfollow_discover_jordan_profile'));
        }
    }
}
