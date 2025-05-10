<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GuideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tags = $this->tags->map(function ($tag) {
            return [
                'name' => $tag->name,
                'slug'=>$tag->slug,
                'image_active' => $tag->getFirstMediaUrl('tag_active', 'tag_active_app'),
                'image_inactive'=> $tag->getFirstMediaUrl('tag_inactive', 'tag_inactive_app'),
            ];
        });

        $gender = [
            'ar'=>[
                1 => 'ذكر', 2=>'انثى'],
            'en'=>[1=>'Male', 2 =>'Female']
        ];
        return [
            'id' => $this->id,
            'slug'=>$this->slug,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'lang' => $this->lang,
            'gender' => $gender[$this->lang][$this->sex],
            'birth_of_day' => $this->birthday,
            'points' => $this->points,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'address' => $this->address,
            'is_guide'=>$this->is_guide,
            'is_following' =>isFollowing($this->id),
            'guide_rating' => $this->guideRatings->avg('rating'),
            'status' => $this->status,
            'description'=>$this->description,
            'following_number' => $this->acceptedFollowing()->count(),
            'follower_number' => $this->acceptedFollowers()->count(),
            'tags'=>$tags,
            'posts'=>UserPostResource::collection($this->posts),
            'reviews'=>ReviewResource::collection($this->reviews),
            'guide_trips'=>AllGuideTripResource::collection($this->guideTrips),
            'visited_places'=> UserVisitedPlaceResource::collection($this->visitedPlace),
            'avatar'=> $this->getFirstMediaUrl('avatar','avatar_app'),
        ];
    }
}
