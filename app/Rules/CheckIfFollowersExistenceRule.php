<?php

namespace App\Rules;

use App\Models\Follow;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfFollowersExistenceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decodedValue = explode(',', $value);

        foreach ($decodedValue as $user) {
            $user = User::where('slug', $user)->value('id');
            if (!Follow::where('follower_id', $user)->where('following_id', Auth::guard('api')->user()->id)->exists()) {
                $fail(__('validation.api.check_if_followers_existence', ['user' => User::find($user)->username]));
            }
        }
    }
}
