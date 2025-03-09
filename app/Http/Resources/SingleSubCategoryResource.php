<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleSubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userLat = $request->lat ? $request->lat : null;
        $userLng = $request->lng ? $request->lng : null;
        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'name' => $this->name,
            'parent' => $this->parent->name ?? null,
            'image_active' => $this->getFirstMediaUrl('category_active', 'category_active_app'),
            'image_inactive' => $this->getFirstMediaUrl('category_inactive', 'category_inactive_app'),
            //            'places' => PlaceResource::collection(
            //                $this->places()
            //                    ->selectRaw('*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance', [$userLat, $userLng, $userLat])
            //                    ->orderBy('distance')
            //                    ->get()
            //            ),
        ];
    }
}
