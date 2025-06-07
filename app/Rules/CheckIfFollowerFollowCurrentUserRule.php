<?php

namespace App\Rules;

use App\Models\Follow;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowerFollowCurrentUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = Auth::guard('api')->user()->id;
        $followerUser = User::findBySlug($value);
        if(!$followerUser)return;
        $exists = Follow::where('follower_id',$followerUser->id)->where('following_id',$id)->exists();
        if(!$exists){
            $fail(__('validation.api.this-user-did-not-follow-you'));
        }
    }
}
