<?php

namespace App\Rules;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Reply;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserCanReplyOnCommentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $comment = Comment::find($value);
        if(!$comment) return;
        $post =$comment?->post;
        if(!$post) return;

        $userId = Auth::guard('api')->user()->id;

        if($post->user_id != $userId ){
            if($post->privacy == 0){
                $fail(__('validation.api.this-post-is-private'));
            }elseif ($post->privacy == 2){
                $is_follower = Follow::where('follower_id',$userId)->where('following_id',$post->user_id)->exists();
                if(!$is_follower){
                    $fail(__('validation.api.you-are-not-following-this-user'));
                }
            }
        }

    }
}
