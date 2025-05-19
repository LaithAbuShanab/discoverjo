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
        $fullName= $this->followingUser->first_name ." ". $this->followingUser->last_name;

        return [
            'id'=>$this->id,
            'following_id'=>$this->followerUser->id,
            'following_slug'=>$this->followingUser->slug,
            'following_name'=>$this->followingUser->username,
            'full_name'=>$fullName,
            'following_image' => $this->followingUser?->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status'=>$this->status,

        ];
    }
}
