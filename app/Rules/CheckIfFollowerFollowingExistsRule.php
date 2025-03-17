<?php

namespace App\Rules;

use App\Models\Follow;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowerFollowingExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $followerId = Auth::guard('api')->user()->id;
        $followingUser = User::findBySlug($value);
        if(!$followingUser) return;
        $exists = Follow::where('follower_id',$followerId)->where('following_id',$followingUser->id)->exists();
        if($exists){
            if(Follow::where('follower_id',$followerId)->where('following_id',$followingUser->id)->where('status',0)->exists())
                $fail(__('validation.api.you_already_make_request_to_this_user_wait_for_accept_id'));
            elseif(Follow::where('follower_id',$followerId)->where('following_id',$followingUser->id)->where('status',1)->exists())
                $fail(__('validation.api.you_already_follow_this_user'));
        }
        if($followingUser->id ==  Auth::guard('api')->user()->id)
            $fail(__('validation.api.you_can_not_follow_yourself'));
    }
}
