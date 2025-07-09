<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SingleServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bookingDate =$this->serviceBookings?->first();
        $categories = $this->categories->map(function ($category) {
            return $category->parent ? [
                'name' => $category->parent->name,
                'main_image' => $category->parent->getFirstMediaUrl('service_main_category','service_main_category_app'),
            ] : null;
        })->filter()->unique();

        $subCategories = $this->categories->map(function ($subcategory) {
            return [
                'name' => $subcategory->name,
                'image_active' => $subcategory->getFirstMediaUrl('service_category_active', 'service_category_active_app'),
                'image_inactive' => $subcategory->getFirstMediaUrl('service_category_inactive', 'service_category_inactive_app'),
            ];
        });

        $requirements = [];
        foreach ($this->requirements as $requirement) {
            $requirements[] = $requirement->item;
        }
        $notes = [];
        foreach ($this->notes as $note) {
            $notes[] = $note->note;
        }
        $activities = [];
        foreach ($this->activities as $activity) {
            $activities[] = $activity->activity;
        }

        $gallery = [];
        foreach ($this->getMedia('service_gallery') as $image) {
            $gallery[] = [
                'id' => $image->id,
                'url' => $image->getUrl('service_gallery_app'),
            ];
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

        $days=[];
        foreach ($bookingDate->serviceBookingDays as $day) {
            $days[] = $day->day_of_week;
        }
        $features = $this->features->map(function ($feature) {
            return [
                'name' => $feature->name,
            ];
        });
        $openingHours = $bookingDate?->serviceBookingDays->map(function ($openingHours) {
            return [
                'day_of_week' => daysTranslation(\Illuminate\Support\Facades\Request::header('Content-Language') ?? 'ar', $openingHours->day_of_week),
                'opening_time' => $openingHours->opening_time,
                'closing_time' => $openingHours->closing_time,
            ];
        });
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->name,
            "description"=>$this->description,
            'available_start_date'=>$bookingDate->available_start_date,
            'available_end_date'=>$bookingDate->available_end_date,
            'session_duration'=>$bookingDate->session_duration,
            'work_days'=>$days,
            'opening_hours' => $openingHours,
            'region'=>new RegionResource($this->region),
            'google_map_url' => $this->url_google_map,
            'category' => $categories,
            'subcategory' => $subCategories,
            'main_price' => $this->price,
            "age_price"=>GuideTripPriceAgeResource::collection($this->priceAges),
            "requirements"=>$requirements,
            "activities"=>$activities,
            'gallery'=>$gallery,
            'notes'=>$notes,
            'features' => $features,
            'provider'=>new ProviderResource($this->provider),
            'is_favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteServices->contains('id', $this->id) : false,
            'rating' => round($total_ratings, 2),
            'total_user_rating' => $total_user_total,
            'reviews' => ReviewResource::collection($filteredReviews),
            'is_creator' => Auth::guard('api')->check() && Auth::guard('api')->user()->id == $this->provider_id,
        ];
    }
}
