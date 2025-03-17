<?php

namespace App\Rules;

use App\Models\Comment;
use App\Models\Post;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserCanDeleteComment implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $comment = Comment::find($value);

        if (!$comment) return;

        $post = Post::find($comment->post_id);
        if(!$post) return;

        if ($comment->user_id !== $userId && (!$post || $post->user_id !== $userId)) {
            $fail(__('validation.api.you_can_not_delete_the_comment'));
        }
    }
}
