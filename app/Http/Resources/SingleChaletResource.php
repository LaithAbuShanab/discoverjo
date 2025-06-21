<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleChaletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $notes = [];
        foreach ($this->notes as $note) {
            $notes[] = $note->note;
        }

        $gallery = [
            $this->getFirstMediaUrl('main_property_image', 'main_property_image_app')
        ];

        foreach ($this->getMedia('property_gallery') as $image) {
            $gallery[] = $image->getUrl('property_gallery_app');
        }
        return [
            'id' => $this->id,
            'name' => $this->name, // Assuming it's a JSON column (multilingual)
            'slug' => $this->slug,
            'description' => $this->description,
            'region'=>new RegionResource($this->region),
            'host_id' => $this->host_id,
            'address' => $this->address,
            'main_image'=> $this->getFirstMediaUrl('main_property_image','main_property_image_app'),
            'gallery' => $gallery,
            'google_map_url' => $this->google_map_url,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'beds' => $this->beds,
            'status' => $this->status,
            'host' => new HostResource($this->host),
            'periods'=> PeriodsResource::collection($this->periods),
            'availabilities'=> AvailabilitiesResource::collection($this->availabilities),
            'notes'=>$notes,
            'amenities' => $this->groupAmenitiesByParent()
        ];
    }

    protected function groupAmenitiesByParent()
    {
        return $this->amenities
            ->filter(fn($amenity) => $amenity->parent) // make sure parent is loaded
            ->groupBy('parent_id')
            ->map(function ($children, $parentId) {
                $parent = $children->first()->parent;

                return [
                    'parent_id' => $parent->id,
                    'parent_slug'=> $parent->slug,
                    'parent_name' => $parent->name,
                    'children' => $children->map(fn($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'icon'=> $child->getFirstMediaUrl('amenity','amenity_app'),
                    ])->values(),
                ];
            })->values();
    }
}
