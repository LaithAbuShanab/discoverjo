<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SinglePostResource extends JsonResource
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

        $filteredComment = $this->comments->filter(function ($comment) {
            return $comment->user->status == 1;
        });

        return [
            'id' => $this->id,
            'visitable_type' => explode("\\Models\\", $this->visitable_type)[1],
            'user' => new UserResource($this->user),
            'created_at' => $this->created_at->diffForHumans(),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePosts->contains('id', $this->id) : false,
            'name' => $this->visitable_type::find($this->visitable_id)?->name,
            'content' => $this->content,
            'images' => $this->getMedia('post')->map(function ($image) {
                return ['id' => $image->id, 'url' => $image->getUrl('post_app'),];
            }),
            'post_likes' => [
                'total_likes' => $filteredLike->count(),
                'user_likes_info' => LikeDislikeResource::collection($filteredLike)
            ],
            'post_dislikes' => [
                'total_disliked' => $filteredDisLike->count(),
                'user_dislikes_info' => LikeDislikeResource::collection($filteredDisLike)
            ],
            'comments' => CommentResource::collection($filteredComment)
        ];
    }
}
