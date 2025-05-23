<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'slug'=>$this->slug,
            'name'=>$this->name,
            'image_active' => $this->getFirstMediaUrl('tag_active', 'tag_active_app'),
            'image_inactive'=> $this->getFirstMediaUrl('tag_inactive', 'tag_inactive_app'),
        ];
    }
}
