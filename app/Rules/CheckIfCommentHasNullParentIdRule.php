<?php

namespace App\Rules;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfCommentHasNullParentIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $comment = Comment::find($value);
        if (!$comment) return;
        if ($comment->parent_id != null) {
            $fail(__('validation.api.the-comment-not-main'));
        }
    }
}
