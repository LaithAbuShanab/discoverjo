<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LikeDislikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fullName= $this->user->first_name ." ". $this->user->last_name;

        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'user_slug' => $this->user->slug,
            'image' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
            'username' => $this->user->username,
            'full_name'=>$fullName,
            'is_following' =>isFollowing($this->user->id),

        ];
    }
}
