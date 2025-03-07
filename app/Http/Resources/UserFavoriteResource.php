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
                'name' => $place->name,
                'image'=>$place->getFirstMediaUrl('main_place', 'main_place_app'),
                'region' => $place->region->name,
                'address' => $place->address,
//                'total_ratings' => $total_ratings == 0 ?$place->rating :$total_ratings,
            ];
        });


        $postFav = $this->favoritePosts->map(function ($post) {
            $gallery = [];
            foreach ($post->getMedia('post') as $image) {
                $gallery[] = $image->original_url;
            }
            return [
                'id'=>$post->id,
                'name' => $post->content,
                'media'=>$gallery,
                'creator'=>$post->user->username,
                'visitable_type'=>explode('\\Models\\', $post->visitable_type)[1],
                'visitable_id'=>$post->visitable_type::find($post->visitable_id)->name,
            ];
        });

        return [
            'places' => $placeFav,
            'trip' => TripResource::collection($this->favoriteTrip),
            'event'=>EventResource::collection($this->favoriteEvent),
            'volunteering'=>VolunteeringResource::collection($this->favoriteVolunteering),
            'plan'=>PlanResource::collection($this->favoritePlans),
            'post'=>$postFav,
            'guide_trip' => GuideFavoriteResource::collection($this->favoriteGuideTrip),
        ];
    }
}
