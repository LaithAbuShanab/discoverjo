<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        // Safely handle missing or inactive user
        if (!$followingUser || $followingUser->status !== 1) {
            return [];
        }

        $fullName = trim("{$followingUser->first_name} {$followingUser->last_name}");

        return [
            'id' => $this->id,
            'following_id' => $followingUser->id,
            'following_slug' => $followingUser->slug,
            'following_name' => $followingUser->username,
            'full_name' => $fullName,
            'following_image' => $followingUser->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status' => $this->status,
        ];
    }

}
