<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideTripPriceAgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'min_age'=>$this->min_age,
            'max_age'=>$this->max_age,
            'price'=>$this->price,
        ];
    }
}
