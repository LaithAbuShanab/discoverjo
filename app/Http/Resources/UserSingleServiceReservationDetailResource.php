<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSingleServiceReservationDetailResource extends JsonResource
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
            'reservation_detail' => $this->reservation_detail, // 1 = adult, 2 = child
            'reservation_detail_label' => $this->reservation_detail == 1 ? 'Adult' : 'Child',
            'quantity' => $this->quantity,
            'price_age_id' => $this->price_age_id,
            'age_range' => optional($this->priceAge)->only(['min_age', 'max_age']), // if relationship is loaded
            'price_per_unit' => (float) $this->price_per_unit,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
