<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleSubCategoryResource extends JsonResource
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
            'name' => $this->name,
            'parent' => $this->parent->name ?? null,
            'image_active' => $this->getFirstMediaUrl('category_active', 'category_active_app'),
            'image_inactive' => $this->getFirstMediaUrl('category_inactive', 'category_inactive_app'),
        ];
    }
}
