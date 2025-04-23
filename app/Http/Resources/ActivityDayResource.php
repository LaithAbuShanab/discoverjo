<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityDayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'name' => $this->activity_name,
            'place'=>$this->place->name,
            'place_slug'=>$this->place->slug,
            'note'=>$this->notes,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'image'=>$this->place->getFirstMediaUrl('main_place', 'main_place_app'),
        ];
    }
}
