<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DaySinglePlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'day_number' => $this->first()->day,
            'activities' => ActivityDayResource::collection($this->flatMap->activities),
        ];
    }
}
