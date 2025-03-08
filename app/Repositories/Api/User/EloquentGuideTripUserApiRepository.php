<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\LegalResource;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\TopTenPlaceResource;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripActivity;
use App\Models\GuideTripAssembly;
use App\Models\GuideTripPriceAge;
use App\Models\GuideTripPriceInclude;
use App\Models\GuideTripRequirement;
use App\Models\GuideTripTrail;
use App\Models\GuideTripUser;
use App\Models\LegalDocument;
use App\Models\Reviewable;
use App\Models\TopTen;
use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;


class EloquentGuideTripUserApiRepository implements GuideTripUserApiRepositoryInterface
{

    public function allUsersForGuideTrip()
    {
        $perPage =15;
        $now = now()->setTimezone('Asia/Riyadh');
        $guideTrip = GuideTrip::with('guide')->with('guideTripUsers')->where('status', '1')->where('end_datetime', '>=', $now)->orderBy('start_datetime')->paginate($perPage);
        //edit the status should has cron job
        GuideTrip::where('status', '1')->where('end_datetime','<',$now)->update(['status' => '0']);

        $tripsArray = $guideTrip->toArray();

        $pagination = [
            'next_page_url'=>$tripsArray['next_page_url'],
            'prev_page_url'=>$tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' =>AllGuideTripResource::collection($guideTrip),
            'pagination' => $pagination
        ];

    }
    public function storeSubscriberInTrip($data)
    {
        $guideTrip = GuideTrip::findOrFail($data['guide_trip_id']);

        $subscribers = array_map(function($subscriber) {
            $subscriber['user_id'] = Auth::guard('api')->user()->id;
            return $subscriber;
        }, $data['subscribers']);

        $guideTrip->guideTripUsers()->createMany($subscribers);

        return ;
    }

    public function updateSubscriberInTrip($data)
    {
        $guideTrip = GuideTrip::findOrFail($data['guide_trip_id']);

        GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id',Auth::guard('api')->user()->id)->delete();
        $subscribers = array_map(function($subscriber) {
            $subscriber['user_id'] = Auth::guard('api')->user()->id;
            return $subscriber;
        }, $data['subscribers']);

        $guideTrip->guideTripUsers()->createMany($subscribers);

        return ;
    }
    public function deleteSubscriberInTrip($id)
    {

        GuideTripUser::where('guide_trip_id', $id)->where('user_id',Auth::guard('api')->user()->id)->delete();

        return ;
    }

    public function allSubscription($id)
    {

        $subscription =GuideTripUser::where('guide_trip_id', $id)->where('user_id',Auth::guard('api')->user()->id)->get();
        return  SubscriptionResource::collection($subscription);
    }
    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteGuideTrip()->attach($id);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteGuideTrip()->detach($id);
    }

    public function addReview($data)
    {
        $filteredContent = app(Pipeline::class)
            ->send($data['comment'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['comment'] = $filteredContent;
        $user = Auth::guard('api')->user();
        $user->reviewGuideTrip()->attach($data['guide_trip_id'], [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);
    }

    public function updateReview($data)
    {
        $filteredContent = app(Pipeline::class)
            ->send($data['comment'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['comment'] = $filteredContent;
        $user = Auth::guard('api')->user();
        $user->reviewGuideTrip()->sync([$data['guide_trip_id'] => [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]]);
    }

    public function deleteReview($id)
    {
        $user = Auth::guard('api')->user();
        $user->reviewGuideTrip()->detach($id);
    }

    public function reviewsLike($request)
    {
        $review = Reviewable::find($request->review_id);
        $status = $request->status == "like" ? '1' : '0';
        $userReview = $review->user;
        $receiverLanguage = $userReview->lang;
        $ownerToken = $userReview->DeviceToken->token;
        $notificationData=[];

        $existingLike = $review->like()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->pivot->status != $status) {
                $review->like()->updateExistingPivot(Auth::guard('api')->user()->id, ['status' => $status]);
                if($request->status == "like"){
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));

                }else{
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];

                    Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user()));
                }
            } else {
                $review->like()->detach(Auth::guard('api')->user()->id);
            }
        } else {
            $review->like()->attach(Auth::guard('api')->user()->id, ['status' => $status]);
            if($request->status == "like"){
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));

            }else{
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];

                Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user()));
            }
        }
        sendNotification($ownerToken, $notificationData);

    }

    public function search($query)
    {
        $perPage =15;

        $trips = GuideTrip::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })->paginate($perPage);


        $tripsArray = $trips->toArray();

        $pagination = [
            'next_page_url'=>$tripsArray['next_page_url'],
            'prev_page_url'=>$tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($trips),
            'pagination' => $pagination
        ];

    }


}
