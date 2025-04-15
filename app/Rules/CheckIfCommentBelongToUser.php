<?php

namespace App\Rules;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfCommentBelongToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $comment = Comment::find($value);
        if (!$comment) {
            return;
        }
        if ($comment->user_id !== Auth::guard('api')->user()->id) {
            $fail(__('validation.api.this-comment-did-not-belong-to-you'));
        }
    }
}
