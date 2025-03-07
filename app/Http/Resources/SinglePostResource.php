<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SinglePostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $images = [];
        foreach ($this->getMedia('post') as $image) {
            $images['id'] = $image->id;
            $images['url'] = $image->getUrl();
        }

        return [
            'content' => $this->content,
            'visitable_type' => explode("\\Models\\", $this->visitable_type)[1],
            'visitable_id' => $this->visitable_id,
            'user' => $this->user->username,
            'avatar' => $this->user->getFirstMediaUrl('avatar', 'avatar_app'),
            'images' => $images,
        ];
    }
}
