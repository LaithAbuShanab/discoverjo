<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllCategoriesResource extends JsonResource
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
            'image' => $this->getFirstMediaUrl('main_category'),
            'priority' => $this->priority,
        ];
    }
}
