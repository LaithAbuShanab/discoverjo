<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllChaletsResource extends JsonResource
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
            'name' => $this->name, // Assuming it's a JSON column (multilingual)
            'slug' => $this->slug,
            'description' => $this->description,
            'region'=>new RegionResource($this->region),
            'address' => $this->address,
            'google_map_url' => $this->google_map_url,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'beds' => $this->beds,
            'status' => $this->status,
            'main_image'=> $this->getFirstMediaUrl('main_property_image','main_property_image_app'),
        ];
    }
}
