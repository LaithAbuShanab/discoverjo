<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredLike = $this->likeDislike->where('status', 1)->filter(function ($like) {
            return $like->user->status == 1;
        });

        $filteredDisLike = $this->likeDislike->where('status', 0)->filter(function ($disLike) {
            return $disLike->user->status == 1;
        });

        return [
            'id' => $this->id,
            'username' => $this->user->username,
            'user_id'=>$this->user->id,
            'user_slug'=>$this->user->slug,
            'avatar' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
            'created_at' => $this->created_at->diffForHumans(),
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'is_following' =>isFollowing($this->user->id),
            'review_likes' => [
                'total_likes' => $filteredLike->count(),
                'user_likes_info' => LikeDislikeResource::collection($filteredLike)
            ],
            'review_dislikes' => [
                'total_disliked' => $filteredDisLike->count(),
                'user_dislikes_info' => LikeDislikeResource::collection($filteredDisLike)
            ]
        ];
    }
}
