<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gallery = [];
        foreach ($this->getMedia('guide_trip_gallery') as $image) {
            $gallery[] =  $image->getUrl('guide_trip_gallery_app');
        }
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->name,
            "description"=>$this->description,
            "start_datetime"=>$this->start_datetime,
            "end_datetime"=>$this->end_datetime,
            "price"=>$this->main_price,
            "max_attendance"=>$this->max_attendance,
            "status"=>$this->status,
            'guide_id'=>$this->guide->id,
            "guide_username"=>$this->guide->username,
            'gallery'=>$gallery,
        ];
    }
}
