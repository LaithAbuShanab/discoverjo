<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllServiceSubCategoriesResource extends JsonResource
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
            'active_image'=>$this->getFirstMediaUrl('service_category_active','service_category_active_app'),
            'inactive_image'=>$this->getFirstMediaUrl('service_category_inactive','service_category_inactive_app'),
        ];
    }
}
