<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\SubscriptionResource;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use App\Notifications\Users\guide\DeleteAllRequestNotification;
use App\Notifications\Users\guide\DeleteRequestNotification;
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

    //    public function updateSubscriberInTrip($data)
    //    {
    //        $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);
    //
    //        GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id', Auth::guard('api')->user()->id)->delete();
    //        $subscribers = array_map(function ($subscriber) {
    //            $subscriber['user_id'] = Auth::guard('api')->user()->id;
    //            return $subscriber;
    //        }, $data['subscribers']);
    //        $joinGuideTrip = $guideTrip->guideTripUsers()->createMany($subscribers);
    //        activityLog('join guide trip', $joinGuideTrip->first(), 'the user update join guide trip', 'update');
    //        return;
    //    }

    public function deleteSubscriberInTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        if (!$guideTrip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        $authUser = Auth::guard('api')->user();

        $guideTripUsers = GuideTripUser::where('guide_trip_id', $guideTrip->id)
            ->where('user_id', $authUser->id)
            ->get();

        if ($guideTripUsers->isEmpty()) {
            return response()->json(['message' => 'No subscriptions found for this user in the trip.'], 200);
        }

        foreach ($guideTripUsers as $guideTripUser) {
            $guideTripUser->delete();
        }

        $guideUser = $guideTrip->user;
        $receiverLanguage = $guideUser->lang ?? 'en';
        $tokens = $guideUser->DeviceTokenMany->pluck('token')->toArray();

        Notification::send($guideUser, new DeleteAllRequestNotification($authUser, $guideTrip));

        $tripName = method_exists($guideTrip, 'getTranslation')
            ? $guideTrip->getTranslation('name', $receiverLanguage)
            : ($receiverLanguage === 'ar' ? $guideTrip->name_ar : $guideTrip->name);

        $notificationData = [
            'title' => Lang::get('app.notifications.delete-user-requests-title', [], $receiverLanguage),
            'body'  => Lang::get('app.notifications.delete-user-requests-body', [
                'username' => $authUser->username,
                'trip'     => $tripName,
            ], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];

        sendNotification($tokens, $notificationData);
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
        $query=  DB::getPdo()->quote($query);
        $trips = GuideTrip::query()
            ->whereHas('guide', function ($query) {
                $query->where('status', 1);
            })
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw(
                    "MATCH(name_en, name_ar) AGAINST (? IN BOOLEAN MODE)",
                    [$query . '*']
                );
            })
            ->paginate($perPage);

        $pagination = [
            'next_page_url' => $trips->nextPageUrl(),
            'prev_page_url' => $trips->previousPageUrl(),
            'total' => $trips->total(),
        ];

        if ($query) {
            activityLog('guide_trip', $trips->first(), $query, 'search');
        }

        return [
            'trips' => AllGuideTripResource::collection($trips),
            'pagination' => $pagination,
        ];
    }


//    public function search( $query)
//    {
//        $perPage = config('app.pagination_per_page');
//        $escapedQuery=DB::getPdo()->quote($query);
//
//        $trips = GuideTrip::where(function ($queryBuilder) use ($escapedQuery) {
//            $queryBuilder->where('name->en', 'like', $escapedQuery)
//                ->orWhere('name->ar', 'like', $escapedQuery)
//                ->orWhere('description->en', 'like', $escapedQuery)
//                ->orWhere('description->ar', 'like', $escapedQuery);
//        })
//            ->whereHas('guide', function ($query) {
//                $query->where('status', '1');
//            })
//            ->paginate($perPage);
//
//        $pagination = [
//            'next_page_url' => $trips->nextPageUrl(),
//            'prev_page_url' => $trips->previousPageUrl(),
//            'total' => $trips->total(),
//        ];
//
//        return [
//            'trips' => AllGuideTripResource::collection($trips),
//            'pagination' => $pagination
//        ];
//    }

    public function updateSingleSubscription($data)
    {
        $guideTripUser = GuideTripUser::find($data['subscription_id']);

        $guideTripUser->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'age' => $data['age'],
            'phone_number' => $data['phone_number']
        ]);

        activityLog('join guide trip', $guideTripUser, 'the user update join guide trip', 'update');
        return;
    }

    public function storeSingleSubscription($data)
    {
        $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);

        $guideTripUser = GuideTripUser::create([
            'guide_trip_id' => $guideTrip->id,
            'user_id' => Auth::guard('api')->user()->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'age' => $data['age'],
            'phone_number' => $data['phone_number'],
            'status' => 0
        ]);

        $guideUser = $guideTrip->user;
        $receiverLanguage = $guideUser->lang;
        $tokens = $guideUser->DeviceTokenMany->pluck('token')->toArray();

        // Save notification in DB
        Notification::send($guideUser, new NewRequestNotification(Auth::guard('api')->user(), $guideTrip));

        $authUser = Auth::guard('api')->user();

        $notificationTitle = Lang::get('app.notifications.delete-request-title', [], $receiverLanguage);
        $notificationBody = Lang::get('app.notifications.delete-request-body', [
            'username' => $authUser->username,
            'trip'     => $guideTrip->name
        ], $receiverLanguage);

        $notificationData = [
            'title' => $notificationTitle,
            'body'  => $notificationBody,
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];


        sendNotification($tokens, $notificationData);

        activityLog('join guide trip', $guideTripUser, 'the user update join guide trip', 'update');
        return;
    }

    public function singleSubscription($id)
    {
        $guideTripUser = GuideTripUser::find($id);
        return $guideTripUser;
    }

    public function deleteSingleSubscription($id)
    {
        $guideTripUser = GuideTripUser::where('id', $id)->first();

        $guideTrip = GuideTrip::where('id', $guideTripUser->guide_trip_id)->first();
        $guideUser = $guideTrip->user;
        $receiverLanguage = $guideUser->lang;
        $tokens = $guideUser->DeviceTokenMany->pluck('token')->toArray();

        // Save notification in DB
        Notification::send($guideUser, new DeleteRequestNotification($guideTrip, $guideTripUser));

        // Get translated trip name
        $tripName = method_exists($guideTrip, 'getTranslation')
            ? $guideTrip->getTranslation('name', $receiverLanguage)
            : ($receiverLanguage === 'ar' ? $guideTrip->name_ar : $guideTrip->name);

        // Send push notification
        $notificationData = [
            'title' => Lang::get('app.notifications.delete-request-title', [], $receiverLanguage),
            'body'  => Lang::get('app.notifications.delete-request-body', [
                'guide_user' =>  $guideTripUser->first_name . ' ' . $guideTripUser->last_name,
                'trip'     => $tripName,
            ], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];

        sendNotification($tokens, $notificationData);

        $guideTripUser->delete();
        return;
    }

    public function dateGuideTrip($date)
    {
        $perPage = config('app.pagination_per_page');
        $query = GuideTrip::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->orderBy('status', 'desc') // status 1 first
            ->orderBy('start_datetime', 'desc');
        $eloquentGuideTrips = GuideTrip::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->orderBy('status', 'desc') // status 1 first
            ->orderBy('start_datetime', 'desc')->paginate($perPage);
        $guideTripsArray = $eloquentGuideTrips->toArray();
        $pagination = [
            'next_page_url' => $guideTripsArray['next_page_url'],
            'prev_page_url' => $guideTripsArray['next_page_url'],
            'total' => $guideTripsArray['total'],
        ];

        activityLog('view guide trip in specific date', $query->first(), 'The user viewed guide trips in specific date ' . $date['date'], 'view');

        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => AllGuideTripResource::collection($eloquentGuideTrips),
            'pagination' => $pagination
        ];
    }
}
