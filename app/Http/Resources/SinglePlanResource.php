<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SinglePlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $creator = $this->creator_type == "App\Models\Admin" ? "admin" : "user";
        return [
            'name' => $this->name,
            'creator' => $creator,
            'description' =>  $this->description,
            'days' => DaySinglePlanResource::collection($this->days->groupBy('day')),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlans->contains('id', $this->id) : false,

        ];
    }
}
