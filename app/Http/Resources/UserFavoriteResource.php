<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Carbon\Carbon;
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

        $placeFav = $this->favoritePlaces->map(function ($place) {
//            $total_ratings = 0;
//            if ($place->total_user_rating > 0 || $place->reviews->count() > 0) {
//                $total_ratings = (($place->total_user_rating * $place->rating) + ($place->reviews->count() * $place->reviews->avg('rating'))) / ($place->total_user_rating + $place->reviews->count());
//            }
            return [
                'id'=>$place->id,
                'slug'=>$place->slug,
                'name' => $place->name,
                'image'=>$place->getFirstMediaUrl('main_place', 'main_place_app'),
                'region' => $place->region->name,
                'address' => $place->address,
//                'total_ratings' => $total_ratings == 0 ?$place->rating :$total_ratings,
            ];
        });


        $postFav = $this->favoritePosts->filter(function ($post) {
            return $post->user->status == 1; // Filter posts where user status is 1
        })->map(function ($post) {
            $gallery = [];
            foreach ($post->getMedia('post') as $image) {
                $gallery[] = $image->original_url;
            }
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

        $tripFav = $this->favoriteTrips->filter(function ($trip) {
            return $trip->user->status == 1;
        });

        $guideTripFav = $this->favoriteGuideTrips->filter(function ($guideTrip) {
            return $guideTrip->guide->status == 1;
        });

        return [
            'places' => $placeFav,
            'trip' => TripResource::collection($tripFav),
            'event'=>EventResource::collection($this->favoriteEvents),
            'volunteering'=>VolunteeringResource::collection($this->favoriteVolunteerings),
            'plan'=>PlanResource::collection($this->favoritePlans),
            'post'=>$postFav,
            'guide_trip' => GuideFavoriteResource::collection($guideTripFav),
        ];
    }
}
