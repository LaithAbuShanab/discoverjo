<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $total_ratings = 0;
        if ($this->total_user_rating > 0 || $this->reviews->count() > 0) {
            $total_ratings = (($this->total_user_rating * $this->rating) + ($this->reviews->count() * $this->reviews->avg('rating'))) / ($this->total_user_rating + $this->reviews->count());
        }

        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'name' => $this->name,
            'image' => $this->getMedia('main_place')->getUrl(),
            'region' => $this->region->name,
            'address' => $this->address,
            'rating' => $this->rating,
            'distance' => $this->distance,
            'total_ratings' => $total_ratings,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlaces->contains('id', $this->id) : false,
            'visited' => Auth::guard('api')->user() ? Auth::guard('api')->user()->visitedPlace->contains('id', $this->id) : false,
        ];
    }
}
