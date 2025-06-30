<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AllServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            'name'=>$this->name,
            'region'=>new RegionResource($this->region),
            'price'=>$this->price,
            'main_image'=> $this->getFirstMediaUrl('main_service','main_service_app'),
            'is_favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteServices->contains('id', $this->id) : false,
        ];
    }
}
