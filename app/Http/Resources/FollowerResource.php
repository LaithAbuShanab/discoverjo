<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FollowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = Auth::guard('api')->user();

        $followerUser = $this->followerUser;




        return [
            'id' => $this->id,
            'follower_id' => $followerUser->id,
            'follower_slug' => $followerUser->slug,
            'follower_name' => $followerUser->username,
            'full_name' => trim("{$followerUser->first_name} {$followerUser->last_name}"),
            'follower_image' => $followerUser->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status' => $this->status,
            'is_following' =>isFollowing($followerUser->id),
        ];
    }

}
