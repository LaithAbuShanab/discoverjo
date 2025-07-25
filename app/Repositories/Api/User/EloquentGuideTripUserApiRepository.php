<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\SubscriptionResource;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use App\Models\Region;
use App\Models\User;
use App\Notifications\Users\guide\DeleteAllRequestNotification;
use App\Notifications\Users\guide\DeleteRequestNotification;
use App\Notifications\Users\guide\NewRequestNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use LevelUp\Experience\Models\Activity;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;

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
                'notification' => [
                    'title' => Lang::get('app.notifications.new-request', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-request-from-trip', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default'
                ],
                "data" => [
                    'type'    => 'guide_trip',
                    'slug'    => $guideTrip->slug,
                    'trip_id' => $guideTrip->id,
                ]
            ];

            if (!empty($tokens))
                sendNotification($tokens, $notificationData);

            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            activityLog('join guide trip', $joinGuideTrip->first(), 'the user join guide trip', 'create');
            return;
        });
    }

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
            'notification' => [
                'title' => Lang::get('app.notifications.delete-user-requests-title', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.delete-user-requests-body', ['username' => $authUser->username, 'trip'     => $tripName,], $receiverLanguage),
                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                'sound' => 'default'
            ],
            "data" => [
                'type'    => 'guide_trip',
                'slug'    => $guideTrip->slug,
                'trip_id' => $guideTrip->id,
            ]
        ];

        if (!empty($tokens))
            sendNotification($tokens, $notificationData);

        $user = Auth::guard('api')->user();
        $user->deductPoints(10);
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

        $trips = GuideTrip::whereHas('guide', function ($q) {
            $q->where('status', '1');
        })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('name_en', 'like', '%' . $query . '%')
                        ->orWhere('name_ar', 'like', '%' . $query . '%');
                });
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

        Notification::send($guideUser, new NewRequestNotification(Auth::guard('api')->user(), $guideTrip));

        $authUser = Auth::guard('api')->user();

        $notificationTitle = Lang::get('app.notifications.new-request', [], $receiverLanguage);
        $notificationBody = Lang::get('app.notifications.new-user-request-from-trip', ['username' => $authUser->username, 'trip' => $guideTrip->name], $receiverLanguage);

        $notificationData = [
            'notification' => [
                'title' => $notificationTitle,
                'body'  => $notificationBody,
                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                'sound' => 'default'
            ],
            "data" => [
                'type'    => 'guide_trip',
                'slug'    => $guideTrip->slug,
                'trip_id' => $guideTrip->id,
            ]
        ];

        if (!empty($tokens))
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
        DB::transaction(function () use ($id) {
            $guideTripUser = GuideTripUser::findOrFail($id);

            $guideTrip = GuideTrip::findOrFail($guideTripUser->guide_trip_id);
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
                'notification' => [
                    'title' => Lang::get('app.notifications.delete-request-title', [], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.delete-request-body', [
                        'guide_user' => $guideTripUser->first_name . ' ' . $guideTripUser->last_name,
                        'trip'       => $tripName,
                    ], $receiverLanguage),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default',
                ],
                "data" => [
                    'type'    => 'guide_trip',
                    'slug'    => $guideTrip->slug,
                    'trip_id' => $guideTrip->id,
                ]
            ];

            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            // Delete user trip subscription
            $guideTripUser->delete();
        });

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

    public function filterGuideTrip($data)
    {
        $perPage = config('app.pagination_per_page');
        $regionId = isset($data['region']) ? Region::findBySlug($data['region'])?->id : null;
        $guideId = isset($data['guide_slug']) ? User::findBySlug($data['guide_slug'])?->id : null;

        $query = GuideTrip::query();
        if (!empty($regionId)) {
            $query = $query->where('region_id', $regionId);
        }
        if (!empty($guideId)) {
            $query = $query->where('guide_id', $guideId);
        }

        $trips = $query->paginate($perPage);
        $tripsArray = $trips->toArray();

        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($trips),
            'pagination' => $pagination
        ];
    }
}
