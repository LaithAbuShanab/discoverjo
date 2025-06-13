<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllServiceCategoriesResource extends JsonResource
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
            'image' => $this->getFirstMediaUrl('service_main_category','service_main_category_app'),
            'priority' => $this->priority,
        ];
    }
}
