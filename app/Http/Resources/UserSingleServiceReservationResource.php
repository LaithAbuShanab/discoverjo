<?php

namespace App\Http\Resources;

use App\Filament\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSingleServiceReservationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'service'=>new AllServicesResource($this->service), // optional, if you load the relation
            'date' => $this->date,
            'start_time' => $this->start_time,
            'contact_info' => $this->contact_info,
            'status' => $this->status,
            'status_label' => $this->status,
            'total_price' => (float) $this->total_price,
            'details'=>UserSingleServiceReservationDetailResource::collection($this->details)
        ];
    }
}
