<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paginationPerPage = config('app.pagination_per_page');

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

        $posts = $this->posts()->paginate($paginationPerPage);
        $reviews = $this->reviews()->paginate($paginationPerPage);
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
            'guide_rating' => $this->is_guide?$this->guideRatings->avg('rating'):false,
            'status' => $this->status,
            'description'=>$this->description,
            'following_number' => $this->acceptedFollowing()->count(),
            'follower_number' => $this->acceptedFollowers()->count(),
            'tags'=>$tags,
            'posts' => [
                'data' => UserPostResource::collection($posts),
                'pagination' => [
                    'next_page_url' => $posts->nextPageUrl(),
                    'prev_page_url' => $posts->previousPageUrl(),
                    'total' => $posts->total(),
                ]
            ],
            'reviews' => [
                'data' => ReviewResource::collection($reviews),
                'pagination' => [
                    'next_page_url' => $posts->nextPageUrl(),
                    'prev_page_url' => $posts->previousPageUrl(),
                    'total' => $posts->total(),
                ]
            ],
            'visited_places'=> UserVisitedPlaceResource::collection($this->visitedPlace),
            'avatar'=> $this->getFirstMediaUrl('avatar','avatar_app'),
        ];
    }
}
