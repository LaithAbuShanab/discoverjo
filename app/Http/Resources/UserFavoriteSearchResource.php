<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFavoriteSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->favorable_type === "App\\Models\\Volunteering") {
            $volunteering = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $volunteering->id,
                'name' => $volunteering->name,
                'description' => $volunteering->description,
            ];
        } elseif ($this->favorable_type === "App\\Models\\Event") {
            $event = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description
            ];
        }elseif ($this->favorable_type === "App\\Models\\Place") {
            $place = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description
            ];
        }elseif ($this->favorable_type === "App\\Models\\Trip") {
            $trip = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $trip->id,
                'name' => $trip->name,
                'description' => $trip->description
            ];
        }elseif ($this->favorable_type === "App\\Models\\Plan") {
            $plan = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description
            ];
        }elseif ($this->favorable_type === "App\\Models\\Post") {
            $post = $this->favorable_type::find($this->favorable_id);
            return [
                "favorable_type" => explode('\\Models\\',$this->favorable_type)[1],
                'id' => $post->id,
                'content' => $post->content,
            ];
        }

        // Handle other cases or return an empty array if none matched
        return [];
    }

}
