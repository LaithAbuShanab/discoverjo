<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->getFirstMediaUrl('slider', 'slider_app'),
//            'priority' => $this->priority,
//            'status'=>$this->status,
//            'type'=>$this->type
        ];
    }
}
