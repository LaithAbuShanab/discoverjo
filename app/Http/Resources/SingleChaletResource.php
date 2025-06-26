<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $total_ratings = 0;
        $total_user_total= 0;
        if ( $this->reviews->count() > 0) {
            $total_ratings =  $filteredReviews->avg('rating');
            $total_user_total = $filteredReviews->count();
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
            'amenities' => $this->groupAmenitiesByParent(),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritepropertys->contains('id', $this->id) : false,
            'rating' =>$total_ratings,
            'total_user_rating' => $total_user_total,
            'reviews' => ReviewResource::collection($filteredReviews),
            'is_creator' => Auth::guard('api')->check() && Auth::guard('api')->user()->id == $this->host_id,
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
