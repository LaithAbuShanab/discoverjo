<?php

namespace App\Rules;

use App\Models\Follow;
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
        $id = Auth::guard('api')->user()->id;
        $exists = Follow::where('follower_id',$id)->where($attribute,$value)->exists();
        if($exists){
            if(Follow::where('follower_id',$id)->where($attribute,$value)->where('status',0)->exists())
                $fail(__('validation.api.you_already_make_request_to_this_user_wait_for_accept_id'));
            elseif(Follow::where('follower_id',$id)->where($attribute,$value)->where('status',1)->exists())
                $fail(__('validation.api.you_already_follow_this_user'));
        }
        if($value ==  Auth::guard('api')->user()->id)
            $fail(__('validation.api.you_can_not_follow_yourself'));
    }
}
