<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PopularPlaceResource extends JsonResource
{
    protected $lat;
    protected $lng;

    public function __construct($resource, $lat = null, $lng = null)
    {
        parent::__construct($resource);
        $this->lat = $lat;
        $this->lng = $lng;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        $userLat = $this->lat;
        $userLng = $this->lng;

        return $this->map(function ($place) use ($userLat, $userLng) {
            $placeLat = $place->place->latitude;
            $placeLng = $place->place->longitude;

            $distance = $userLat && $userLng ? haversineDistance($userLat, $userLng, $placeLat, $placeLng) : null;

            return [
//                'id' => $place->id,
                'place_id' => $place->place->id,
                'place_slug'=>$place->place->slug,
                'name' => $place->place->name,
                'description'=>$place->place->description,
                'image' => $place->place->getFirstMediaUrl('main_place','main_place_app'),
                'region' => $place->place->region->name,
                'address' => $place->place->address,
                'rating' => $place->place->rating,
                'local_price' => $place->local_price,
                'foreign_price'=>$place->foreign_price,
                'distance' => $distance,
            ];
        });
    }



}
