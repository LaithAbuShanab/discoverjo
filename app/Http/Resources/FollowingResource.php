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
        return [
            'follower_id'=>$this->follower_id,
            'follower_name'=>$this->followingUser->username,
            'follower_image' => $this->followingUser?->getMedia('avatar')->first()?->getUrl(),
            'status'=>$this->status,

        ];
    }
}
