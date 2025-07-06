<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyReservationDetailResrource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period_type'=>$this->period->type,
            'from_datetime'=>$this->from_datetime,
            'to_datetime'=>$this->to_datetime,
            'price'=>$this->price
        ];
    }
}
