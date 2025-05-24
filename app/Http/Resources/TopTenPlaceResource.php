<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TopTenPlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request)
    {
            return [
                'place_id' => $this->place->id,
                'place_slug'=>$this->place->slug,
                'name' => $this->place->name,
                'description'=>$this->place->description,
                'image' => $this->place->getFirstMediaUrl('main_place'),
                'region' => $this->place->region->name,
                'address' => $this->place->address,
                'rating' => $this->place->rating,
                'rank' => $this->rank,
                'status'=>$this->place->status,
                'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlaces->contains('id',  $this->place) : false,
            ];
    }





}
