<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GamePlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this?->getFirstMediaUrl('main_place'),
            'region' => $this->region->name,
            'address' => $this->address,
            'rating' => $this->rating,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlaces->contains('id', $this->id) : false,
        ];
    }
}
