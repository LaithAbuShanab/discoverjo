<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPropertyReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "property" => new AllChaletsResource($this->property),
            "check_in" => $this->check_in,
            "check_out" => $this->check_out,
            "total_price" => $this->total_price,
            "status" => $this->status,
            "reservation_details"=>PropertyReservationDetailResrource::collection($this->details)
        ];
    }
}
