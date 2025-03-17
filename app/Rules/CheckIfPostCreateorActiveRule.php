<?php

namespace App\Rules;

use App\Models\Post;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfPostCreateorActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $post = Post::find($value);
        if(!$post) return;
        $activeOwner = $post->user?->status;
        if(!$activeOwner){
            $fail(__('validation.api.the-user-post-creator-not-longer-active'));
        }
    }
}
