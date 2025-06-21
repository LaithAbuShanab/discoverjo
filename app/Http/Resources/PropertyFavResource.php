<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyFavResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->name,
            'main_image'=> $this->getFirstMediaUrl('main_property_image','main_property_image_app'),
            "description"=>$this->description,
            "status"=>$this->status,
            'host_id'=>$this->host->id,
            "host_username"=>$this->host->username,
        ];
    }
}
