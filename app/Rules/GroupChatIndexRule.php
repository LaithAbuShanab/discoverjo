<?php

namespace App\Rules;

use App\Models\Conversation;
use App\Models\GroupMember;
use App\Models\Trip;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class GroupChatIndexRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $conversationId = $value;

        // Check If The Conversation Is Available
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            if (!$conversation || Trip::where('id', $conversation->trip_id)->where('status', '!=', '1')->exists()) {
                $fail(__('validation.api.this-conversation-is-not-available'));
                return;
            }

            // Check If The User Is A Member Of The Conversation
            if (GroupMember::where('conversation_id', $conversationId)->where('user_id', Auth::guard('api')->user()->id)->doesntExist()) {
                $fail(__('validation.api.you-are-not-a-member-of-this-conversation'));
            }


            // Check If The User Is A Member Of The Conversation
            if (GroupMember::where('conversation_id', $conversationId)->where('user_id', Auth::guard('api')->user()->id)->where('left_datetime', null)->doesntExist()) {
                $fail(__('validation.api.you-are-not-a-member-of-this-conversation'));
            }
        }
    }
}
