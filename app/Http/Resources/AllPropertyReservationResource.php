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

        if ($this->period?->type != 1) {
            $this->check_out = date('Y-m-d', strtotime( $this->check_out . ' +1 day'));
        }


        return [
            "id" => $this->id,
            "property" => new AllChaletsResource($this->property),
            "period_type" => $this->period?->type,
            "check_in" => $this->check_in,
            "check_out" => $this->check_out,
            "total_price" => $this->total_price,
            "status" => $this->status,
        ];
    }
}
