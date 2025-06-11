<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FollowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $followingUser = $this->followingUser;


        $fullName = trim("{$followingUser->first_name} {$followingUser->last_name}");

        return [
            'id' => $this->id,
            'following_id' => $followingUser->id,
            'following_slug' => $followingUser->slug,
            'following_name' => $followingUser->username,
            'full_name' => $fullName,
            'following_image' => $followingUser->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status' => $this->status,
            'is_following' =>isFollowing($followingUser->id),
            'is_follow_me' => isFollower($followingUser->id),
        ];
    }

}
