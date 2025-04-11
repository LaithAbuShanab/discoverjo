<?php

namespace App\Rules;

use App\Models\BlockedUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckUserInBlackListRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the value exists in the BlockedUser model
        if (BlockedUser::where($attribute, $value)->exists()) {
            $fail(__('validation.api.this_email_in_black_list'));
        }
    }
}
