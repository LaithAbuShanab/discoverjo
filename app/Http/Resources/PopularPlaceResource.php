<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PopularPlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
            return [
//                'id' => $this->id,
                'place_id' => $this->place->id,
                'place_slug'=>$this->place->slug,
                'name' => $this->place->name,
                'description'=>$this->place->description,
                'image' => $this->place->getFirstMediaUrl('main_place','main_place_app'),
                'region' => $this->place->region->name,
                'address' => $this->place->address,
                'rating' => $this->place->rating,
                'local_price' => $this->local_price,
                'foreign_price'=>$this->foreign_price,
                'distance' => $this->distance,
            ];
    }



}
