<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserPostResource extends JsonResource
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
        $visitable = $this->visitable_type::find($this->visitable_id);
        return [
            'id' => $this->id,
            'visitable_type' => explode("\\Models\\", $this->visitable_type)[1],
            'user' => new UserResource($this->user),
            'created_at' => $this->created_at->diffForHumans(),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePosts->contains('id', $this->id) : false,
            'name' => $visitable?->name,
            'slug' => $visitable?->slug,
            'content' => $this->content,
            'images' => $this->getMedia('post')->map(function ($media) {
                $url = $media->hasGeneratedConversion('post_app')
                    ? $media->getUrl('post_app')
                    : $media->getUrl();

                return [
                    'id' => $media->id,
                    'url' => $url,
                ];
            }),

            'post_likes' => [
                'total_likes' => $filteredLike->count(),
                'user_likes_info' => LikeDislikeResource::collection(
                    $filteredLike->reject(function ($like) {
                        $currentUser = Auth::guard('api')->user();
                        if (!$currentUser) return false;
                        return $currentUser->blockedUsers->contains('id', $like->user_id) ||
                            $currentUser->blockers->contains('id', $like->user_id);
                    })
                )

            ],
            'post_dislikes' => [
                'total_disliked' => $filteredDisLike->count(),
                'user_dislikes_info' => LikeDislikeResource::collection(
                    $filteredDisLike->reject(function ($like) {
                        $currentUser = Auth::guard('api')->user();
                        if (!$currentUser) return false;
                        return $currentUser->blockedUsers->contains('id', $like->user_id) ||
                            $currentUser->blockers->contains('id', $like->user_id);
                    })
                )
            ],
            'comments' => CommentResource::collection(
                $filteredComment->reject(function ($comment) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $comment->user_id) ||
                        $currentUser->blockers->contains('id', $comment->user_id);
                })
            ),
        ];
    }
}
