<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class IsConversationMemberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $conversation = \App\Models\Conversation::find($value);

        // check if auth user is a member of the conversation
        $conversation->members()->where('user_id', Auth::guard('api')->user()->id)->first() ?: $fail(__('validation.api.you-are-not-a-member-of-this-conversation'));
    }
}
