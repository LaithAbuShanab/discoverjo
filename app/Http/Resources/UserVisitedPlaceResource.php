<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserVisitedPlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'=>$this->name,
            'slug'=>$this->slug,
            'main_image' => $this->getFirstMediaUrl('main_place','main_place_app'),
            'longitude'=>$this->longitude,
            'latitude'=>$this->latitude

        ];
    }
}
