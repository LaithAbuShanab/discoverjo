<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $filteredLike = $this->likes->where('status', 1)->filter(function ($like) {
            return $like->user->status == 1;
        });

        $filteredDisLike = $this->likes->where('status', 0)->filter(function ($disLike) {
            return $disLike->user->status == 1;
        });
        $fullName= $this->user->first_name ." ". $this->user->last_name;

        return [
            'id' => $this->id,
            'username' => $this->user->username,
            'user_slug' => $this->user->slug,
            'full_name'=>$fullName,
            'avatar' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
            'is_following' =>isFollowing($this->user->id),
            'created_at' => $this->created_at->diffForHumans(),
            'content' => $this->content,
            'reply_likes' => [
                'total_likes' => $filteredLike->count(),
                'user_likes_info' => LikeDislikeResource::collection($filteredLike)
            ],
            'reply_dislikes' => [
                'total_disliked' => $filteredDisLike->count(),
                'user_dislikes_info' => LikeDislikeResource::collection($filteredDisLike)
            ],
            // 'replies' => ReplyResource::collection($this->replies)
        ];
    }
}
