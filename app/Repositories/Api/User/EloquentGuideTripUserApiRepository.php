<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\SubscriptionResource;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use Illuminate\Support\Facades\Auth;

class EloquentGuideTripUserApiRepository implements GuideTripUserApiRepositoryInterface
{

    public function allUsersForGuideTrip()
    {
        $perPage =config('app.pagination_per_page');
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
            'next_page_url'=>$tripsArray['next_page_url'],
            'prev_page_url'=>$tripsArray['next_page_url'],
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
        $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);

        $subscribers = array_map(function($subscriber) {
            $subscriber['user_id'] = Auth::guard('api')->user()->id;
            return $subscriber;
        }, $data['subscribers']);

        $joinGuideTrip = $guideTrip->guideTripUsers()->createMany($subscribers);

        activityLog('Guide trip user',$joinGuideTrip->first(), 'the user join guide trip','create');

        return ;
    }

    public function updateSubscriberInTrip($data)
    {
        $guideTrip = GuideTrip::findBySlug($data['guide_trip_slug']);

        GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id',Auth::guard('api')->user()->id)->delete();
        $subscribers = array_map(function($subscriber) {
            $subscriber['user_id'] = Auth::guard('api')->user()->id;
            return $subscriber;
        }, $data['subscribers']);
        $joinGuideTrip =$guideTrip->guideTripUsers()->createMany($subscribers);
        activityLog('Guide trip user',$joinGuideTrip->first(), 'the user update join guide trip','update');


        return ;
    }

    public function deleteSubscriberInTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $guideTripUser =GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id',Auth::guard('api')->user()->id)->first();
        $guideTripUser->delete();
        return ;
    }

    public function allSubscription($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $subscription =GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('user_id',Auth::guard('api')->user()->id)->get();
        activityLog('Guide trip user',$subscription->first(), 'the user viewed join guide trip','view');

        return  SubscriptionResource::collection($subscription);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');

        $trips = GuideTrip::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->whereHas('guide', function ($query) {
                $query->where('status', '1');
            })
            ->paginate($perPage);



        $tripsArray = $trips->toArray();

        $pagination = [
            'next_page_url'=>$tripsArray['next_page_url'],
            'prev_page_url'=>$tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];
        activityLog('Guide Trip',$trips->first(),$query,'Search');

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($trips),
            'pagination' => $pagination
        ];

    }


}
