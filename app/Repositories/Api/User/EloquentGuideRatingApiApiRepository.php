<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\ShowGuideRatingResource;
use App\Interfaces\Gateways\Api\User\GuideRatingApiRepositoryInterface;

use App\Models\Post;
use App\Models\RatingGuide;
use App\Models\Trip;
use App\Models\User;

use App\Notifications\Users\post\NewPostDisLikeNotification;
use App\Notifications\Users\post\NewPostLikeNotification;
use App\Notifications\Users\Trip\NewRequestNotification;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentGuideRatingApiApiRepository implements GuideRatingApiRepositoryInterface
{
    public function createGuideRating($data)
    {
        $createGuideRating = RatingGuide::create($data);
    }
    public function updateGuideRating($data)
    {

        $updateRatingGuide = RatingGuide::where('guide_id',$data['guide_id'])->where('user_id',$data['user_id'])->first();
        $updateRatingGuide->update([
            'rating'=>$data['rating']
        ]);

    }
    public function deleteGuideRating($slug)
    {
        $userId = Auth::guard('api')->user()->id;
        $guide =User::findBySlug($slug);
        $deleteRatingGuide = RatingGuide::where('guide_id',$guide->id)->where('user_id',$userId)->delete();

    }

    public function showGuideRating($slug)
    {
        $userId = Auth::guard('api')->user()->id;
        $guide = User::findBySlug($slug);
        $RatingGuide = RatingGuide::where('guide_id',$guide->id)->where('user_id',$userId)->first();
        return new ShowGuideRatingResource($RatingGuide);

    }





}
