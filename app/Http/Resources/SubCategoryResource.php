<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'priority' => $this->priority,
            'image_active' => $this->getFirstMediaUrl('subcategory_active', 'subcategory_active_app'),
            'image_inactive' => $this->getFirstMediaUrl('subcategory_inactive', 'subcategory_inactive_app'),
        ];
    }
}
