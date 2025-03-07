<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturesResource extends JsonResource
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
            'name' => $this->name,
            'active_image' => $this->getFirstMediaUrl('feature_active', 'feature_active_app'),
            'inactive_image' => $this->getFirstMediaUrl('feature_inactive', 'feature_inactive_app'),
        ];
    }
}
