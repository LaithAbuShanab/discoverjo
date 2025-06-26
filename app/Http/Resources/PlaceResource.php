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
        $total_user_total= 0;
        if ($this->total_user_rating > 0 || $this->reviews->count() > 0) {
            $total_ratings = (($this->total_user_rating * $this->rating) + ($this->reviews->count() * $this->reviews->avg('rating'))) / ($this->total_user_rating + $this->reviews->count());
            $total_user_total = $this->total_user_rating  + $this->reviews->count();
        }

        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'name' => $this->name,
            'image' => $this->getFirstMediaUrl('main_place','main_place_app'),
            'region' => $this->region->name,
            'address' => $this->address,
            'distance' => $this->distance,
            'total_ratings' => $total_ratings,
            'rating' =>$total_ratings,
            'total_user_rating' => $total_user_total,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlaces->contains('id', $this->id) : false,
            'visited' => Auth::guard('api')->user() ? Auth::guard('api')->user()->visitedPlace->contains('id', $this->id) : false,
        ];
    }
}
