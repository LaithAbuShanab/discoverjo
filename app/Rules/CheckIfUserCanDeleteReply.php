<?php

namespace App\Rules;

use App\Models\Post;
use App\Models\Reply;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserCanDeleteReply implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $reply = Reply::find($value);
        if (!$reply) {
            $fail(__('validation.api.reply_not_found'));
            return;
        }
        $post = Post::find($reply->comment->post_id);

        if(!$reply->user_id == $userId && !$post->user_id !== $userId){
            $fail(__('validation.api.you_can_not_delete_the_reply'));
        }
    }
}
