<?php

namespace App\Rules;

use App\Models\Follow;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowerFollowingUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user =  Auth::guard('api')->user();
        $id = $user->id;
        $followerUser = User::findBySlug($value);
        if (!$followerUser) return;
        // Check if there is no request belonging to this user as a follower
        $exists = Follow::where('following_id', $id)->where('follower_id', $followerUser->id)->exists();
        if (!$exists) {
            $fail(__('validation.api.there_is_noting_request_belong_to_this_user_as_follower'));
            return;
        }

        // Check if the user already follows the current user
        if (Follow::where('following_id', $id)->where('follower_id', $followerUser->id)->where('status', 1)->exists()) {
            $fail(__('validation.api.this_user_already_follow_you'));
            return;
        }

        // Prevent sending a request to oneself
        if ($user->slug == $value) {
            $fail(__('validation.api.you_can_not_make_request_to_yourself'));
        }
    }
}
