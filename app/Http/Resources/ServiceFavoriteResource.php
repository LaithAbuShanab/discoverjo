<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gallery = [];
        foreach ($this->getMedia('service_gallery') as $image) {
            $gallery[] =  $image->getUrl();
        }
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->name,
            'main_image'=> $this->getFirstMediaUrl('main_service','main_service_app'),
            "description"=>$this->description,
            "price"=>$this->price,
            "status"=>$this->status,
            'provider_id'=>$this->provider->id,
            "provider_username"=>$this->provider->username,
            'gallery'=>$gallery,
        ];
    }
}
