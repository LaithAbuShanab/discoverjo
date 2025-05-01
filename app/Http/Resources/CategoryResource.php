<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $userLat = $request->lat ? $request->lat : null;
        $userLng = $request->lng ? $request->lng : null;


        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'name' => $this->name,
            'active_image'=>$this->getFirstMediaUrl('category_active'),
            'inactive_image'=>$this->getFirstMediaUrl('category_inactive'),
//            'places' => PlaceResource::collection(
//                $this->places->placesThrough()
//                    ->selectRaw('*,places.id,places.name,places.address,places.rating, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance', [$userLat, $userLng, $userLat])
//                    ->orderBy('distance')
//                    ->get()
//            ),

        ];
    }
}
