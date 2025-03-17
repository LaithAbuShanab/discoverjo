<?php

namespace App\Rules;

use App\Models\Follow;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowerFollowingNotExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = Auth::guard('api')->user()->id;
        $followingUser = User::findBySlug($value);
        if(!$followingUser)return;
        $exists = Follow::where('follower_id',$id)->where('following_id',$followingUser->id)->exists();
        if(!$exists){
            $fail(__('validation.api.you_are_not_follower_for_this_user'));
        }
    }
}
