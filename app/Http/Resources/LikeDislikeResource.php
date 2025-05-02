<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeDislikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'user_slug' => $this->user->slug,
            'image' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
            'username' => $this->user->username,
        ];
    }
}
