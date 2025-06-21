<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilitiesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type'=>$this->type,
            'availability_start_date'=>$this->availability_start_date,
            'availability_end_date'=>$this->availability_end_date,
            'days'=>AvailabilityDaysResource::collection($this->availabilityDays)
        ];
    }
}
