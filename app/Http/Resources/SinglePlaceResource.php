<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SinglePlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $userLat = $request->lat ? $request->lat : null;
        $userLng = $request->lng ? $request->lng : null;
        $placeLat = $this->latitude;
        $placeLng = $this->longitude;


        $tags = $this->tags->map(function ($tag) {
            return [
                'name' => $tag->name,
                'image_active' => $tag->getFirstMediaUrl('tag_active', 'tag_active_app'),
                'image_inactive' => $tag->getFirstMediaUrl('tag_inactive', 'tag_inactive_app'),
            ];
        });

        $subCategories = $this->categories->map(function ($subcategory) {
            return [
                'name' => $subcategory->name,
                'image_active' => $subcategory->getFirstMediaUrl('category_active', 'category_active_app'),
                'image_inactive' => $subcategory->getFirstMediaUrl('category_inactive', 'category_inactive_app'),
            ];
        });

        $categories = $this->categories->map(function ($category) {
            return $category->parent ? [
                'name' => $category->parent->name,
                'main_image' => $category->parent->getFirstMediaUrl('main_category', 'main_category_app'),
            ] : null;
        })->filter()->unique();

        $features = $this->features->map(function ($feature) {
            return [
                'name' => $feature->name,
                'image_active' => $feature->getFirstMediaUrl('feature_active', 'feature_active_app'),
                'image_inactive' => $feature->getFirstMediaUrl('feature_inactive', 'feature_inactive_app'),
            ];
        });

        $openingHours = $this->openingHours->map(function ($openingHours) {
            return [
                'day_of_week' => daysTranslation(Request::header('Content-Language') ?? 'ar', $openingHours->day_of_week),
                'opening_time' => $openingHours->opening_time,
                'closing_time' => $openingHours->closing_time,
            ];
        });

        $gallery = [
            $this->getFirstMediaUrl('main_place', 'main_place_app')
        ];

        foreach ($this->getMedia('place_gallery') as $image) {
            $gallery[] = $image->getUrl('place_gallery_app');
        }

        $posts = $this->posts->filter(function ($post) {
            if ($post->privacy == 0) {
                $userId = Auth::guard('api')->user()->id;
                return $post->user_id == $userId;
            }
            if ($post->privacy == 1) {
                return true;
            }

            // Followers-only post
            if ($post->privacy == 2) {
                // Assuming you have access to the authenticated user
                $user = Auth::guard('api')->user();
                $owner = $user->id == $post->user_id;
                return $owner || $user->isFollowing($post->user); // or $post->creator
            }

            return false;
        })->sortByDesc('id')->values();

        $weather = null;
        $temp = getWeatherNow($placeLat, $placeLng);

        if ($temp !== false) {
            $weather = $temp;
        }

        $distance = $userLat && $userLng ? haversineDistance($userLat, $userLng, $placeLat, $placeLng) : null;

        $total_ratings = 0;
        $total_user_total = 0;
        if ($this->total_user_rating > 0 || $this->reviews->count() > 0) {
            $total_ratings = (($this->total_user_rating * $this->rating) + ($this->reviews->count() * $this->reviews->avg('rating'))) / ($this->total_user_rating + $this->reviews->count());
            $total_user_total = $this->total_user_rating  + $this->reviews->count();
        }

        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->getFirstMediaUrl('main_place', 'main_place_app'),
            'region' => $this->region->name,
            'address' => $this->address,
            'rating' => round($total_ratings, 2),
            'total_user_rating' => $total_user_total,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'weather' => $weather,
            'business_status' => businessStatusTranslation(Request::header('Content-Language') ?? 'ar', $this->business_status),
            'google_map_url' => $this->google_map_url,
            'phone_number' => $this->phone_number,
            'price_level' => $this->price_level,
            'website' => $this->website,
            'distance' => $distance,
            'category' => $categories,
            'subcategory' => $subCategories,
            'opening_hours' => $openingHours,
            'features' => $features,
            'tags' => $tags,
            'gallery' => $gallery,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoritePlaces->contains('id', $this->id) : false,
            'visited' => Auth::guard('api')->user() ? Auth::guard('api')->user()->visitedPlace->contains('id', $this->id) : false,
            'reviews' => ReviewResource::collection(
                $filteredReviews->reject(function ($review) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $review->user_id) ||
                        $currentUser->blockers->contains('id', $review->user_id);
                })
            ),
            'posts' => UserPostResource::collection(
                $posts->reject(function ($post) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $post->user_id) ||
                        $currentUser->blockers->contains('id', $post->user_id);
                })
            ),
        ];
    }
}
