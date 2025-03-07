<?php

namespace App\Rules;

use App\Models\Follow;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowerFollowingUserWithAnyStatusRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = Auth::guard('api')->user()->id;

        // Check if there is no request belonging to this user as a follower
        $exists = Follow::where('following_id', $id)->where($attribute, $value)->exists();
        if (!$exists) {
            $fail(__('validation.api.there_is_noting_request_belong_to_this_user_as_follower'));
        }

        // Prevent sending a request to oneself
        if ($id == $value) {
            $fail(__('validation.api.you_can_not_make_request_to_yourself'));
        }
    }

}
