<?php

namespace App\Rules;

use App\Models\Reply;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfReplyBelongToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $reply = Reply::find($value);
        if (!$reply) return;

        // Check if the authenticated user is the owner of the reply
        if ($reply->user_id !== Auth::guard('api')->user()->id) {
            $fail(__('validation.api.this_reply_did_not_belong_to_you'));
        }
    }
}
