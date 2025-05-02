<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $placeFav = $this->favoritePlaces
            ->filter(fn($place) => $place->status == 1)
            ->map(function ($place) {
                return [
                    'id'      => $place->id,
                    'slug'    => $place->slug,
                    'name'    => $place->name,
                    'image'   => $place->getFirstMediaUrl('main_place', 'main_place_app'),
                    'region'  => $place->region->name,
                    'address' => $place->address,
                ];
            });


        $postFav = $this->favoritePosts
            ->filter(fn($post) => $post->user->status == 1)
            ->map(function ($post) {
                $gallery = $post->getMedia('post')->map(fn($image) => $image->getUrl('post_app'))->toArray();

                return [
                    'id' => $post->id,
                    'name' => $post->content,
                    'media' => $gallery,
                    'creator_id' => $post->user->id,
                    'creator_username' => $post->user->username,
                    'creator_slug' => $post->user->slug,
                    'visitable_type' => explode('\\Models\\', $post->visitable_type)[1],
                    'visitable_id' => $post->visitable_type::find($post->visitable_id)->name,
                ];
            });

        $tripFav = $this->favoriteTrips->filter(fn($trip) => $trip->user->status == 1);

        $guideTripFav = $this->favoriteGuideTrips->filter(fn($guideTrip) => $guideTrip->guide->status == 1);

        $planFav = $this->favoritePlans->filter(function ($plan) {
            if ($plan->creator_type === 'App\\Models\\User') {
                return $plan->creator && $plan->creator->status == 1;
            }

            // Allow all plans if the creator is an admin
            if ($plan->creator_type === 'App\\Models\\Admin') {
                return true;
            }

            // Optional: exclude plans with unknown creator types
            return false;
        });


        return [
            'places'       => $placeFav,
            'trip'         => TripResource::collection($tripFav),
            'event'        => EventResource::collection($this->favoriteEvents),
            'volunteering' => VolunteeringResource::collection($this->favoriteVolunteerings),
            'plan'         => PlanResource::collection($planFav),
            'post'         => $postFav,
            'guide_trip'   => GuideFavoriteResource::collection($guideTripFav),
        ];
    }
}
