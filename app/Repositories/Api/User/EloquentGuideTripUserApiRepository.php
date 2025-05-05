<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\SubscriptionResource;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use App\Notifications\Users\guide\NewRequestNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use LevelUp\Experience\Models\Activity;

class EloquentGuideTripUserApiRepository implements GuideTripUserApiRepositoryInterface
{

    public function allUsersForGuideTrip()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $guidesTrips = GuideTrip::where('status', 1)
            ->where('start_datetime', '>', $now)
            ->whereHas('guide', function ($query) {
                $query->where('status', '1'); // Ensures only trips where the guide is active
            })
            ->orderBy('start_datetime')
            ->paginate($perPage);

        GuideTrip::where('status', '1')->where('start_datetime', '<', $now)->update(['status' => '0']);

        $tripsArray = $guidesTrips->toArray();

        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($guidesTrips),
            'pagination' => $pagination
        ];
    }

    public function storeSubscriberInTrip($data)
    {
        return DB::transaction(function () use ($data) {

            $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);

            $subscribers = array_map(function ($subscriber) {
                $subscriber['user_id'] = Auth::guard('api')->user()->id;
                return $subscriber;
            }, $data['subscribers']);

            $joinGuideTrip = $guideTrip->guideTripUsers()->createMany($subscribers);


            $guideUser = $guideTrip->user;
            $receiverLanguage = $guideUser->lang;
            $tokens = $guideUser->DeviceTokenMany->pluck('token')->toArray();

            // Save notification in DB
            Notification::send($guideUser, new NewRequestNotification(Auth::guard('api')->user(), $guideTrip));

            // Send push notification
            $notificationData = [
                'title' => Lang::get('app.notifications.new-request', [], $receiverLanguage),
                'body' => Lang::get('app.notifications.new-user-request-from-trip', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                'icon'  => asset('assets/icon/trip.png'),
                'sound' => 'default',
            ];

            sendNotification($tokens, $notificationData);

            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            activityLog('join guide trip', $joinGuideTrip->first(), 'the user join guide trip', 'create');
            return;
        });
    }

    public function updateSubscriberInTrip($data)
    {
        $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);

        GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id', Auth::guard('api')->user()->id)->delete();
        $subscribers = array_map(function ($subscriber) {
            $subscriber['user_id'] = Auth::guard('api')->user()->id;
            return $subscriber;
        }, $data['subscribers']);
        $joinGuideTrip = $guideTrip->guideTripUsers()->createMany($subscribers);
        activityLog('join guide trip', $joinGuideTrip->first(), 'the user update join guide trip', 'update');
        return;
    }

    public function deleteSubscriberInTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);

        $guideTripUser = GuideTripUser::where('guide_trip_id', $guideTrip->id)
            ->where('user_id', Auth::guard('api')->user()->id)
            ->get();

        if ($guideTripUser) {
            $guideTripUser->delete();
        }
    }


    public function allSubscription($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $subscription = GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id', Auth::guard('api')->user()->id)->get();
        activityLog('Guide trip user', $subscription->first(), 'the user viewed his joined guide trip request', 'view');

        return  SubscriptionResource::collection($subscription);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');
        $escapedQuery = '%' . addcslashes($query, '%_') . '%';
        $trips = GuideTrip::where(function ($queryBuilder) use ($escapedQuery) {
            $queryBuilder->where('name->en', 'like', $escapedQuery)
                ->orWhere('name->ar', 'like', $escapedQuery)
                ->orWhere('description->en', 'like', $escapedQuery)
                ->orWhere('description->ar', 'like', $escapedQuery);
        })
            ->whereHas('guide', function ($query) {
                $query->where('status', '1');
            })
        ->paginate($perPage);


        $tripsArray = $trips->toArray();

        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        if($query) {
            activityLog('search for guide trips', $trips->first(), $query, 'Search');
        }
        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($trips),
            'pagination' => $pagination
        ];
    }
}
