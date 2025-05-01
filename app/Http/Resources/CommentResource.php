<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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

        $filteredReply = $this->replies->filter(function ($reply) {
            return $reply->user->status == 1;
        });

        return [
            'id' => $this->id,
            'username' => $this->user->username,
            'user_slug' => $this->user->slug,
            'avatar' => $this->user->getFirstMediaUrl('avatar'),
            'created_at' => $this->created_at->diffForHumans(),
            'content' => $this->content,
            'comment_likes' => [
                'total_likes' => $filteredLike->count(),
                'user_likes_info' => LikeDislikeResource::collection($filteredLike)
            ],
            'comment_dislikes' => [
                'total_disliked' => $filteredDisLike->count(),
                'user_dislikes_info' => LikeDislikeResource::collection($filteredDisLike)
            ],
            'replies' => ReplyResource::collection($filteredReply)
        ];
    }
}
