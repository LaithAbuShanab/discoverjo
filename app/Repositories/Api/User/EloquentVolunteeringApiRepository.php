<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\SingleEventResource;
use App\Http\Resources\SingleVolunteeringResource;
use App\Http\Resources\VolunteeringResource;
use App\Interfaces\Gateways\Api\User\EventApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\VolunteeringApiRepositoryInterface;
use App\Models\Category;
use App\Models\Event;
use App\Models\Reviewable;
use App\Models\User;
use App\Models\Volunteering;
use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use LevelUp\Experience\Models\Activity;


class EloquentVolunteeringApiRepository implements VolunteeringApiRepositoryInterface
{
    public function getAllVolunteerings()
    {
        $perPage =config('app.pagination_per_page');
        $eloquentVolunteerings = Volunteering::orderBy('status','desc') // status 1 first
        ->orderBy('start_datetime', 'desc')             // then order by start_datetime
        ->paginate($perPage);

        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }

    public function activeVolunteerings()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh');
        $eloquentVolunteerings = Volunteering::orderBy('start_datetime')->where('status', '1')->where('end_datetime', '>=', $now)->paginate($perPage);
        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }

    public function volunteering($slug)
    {
        $eloquentVolunteerings = Volunteering::findBySlug($slug);
        activityLog('view specific volunteering',$eloquentVolunteerings,'The user viewed volunteering','view');
        return new SingleVolunteeringResource($eloquentVolunteerings);
    }

    public function dateVolunteerings($date)
    {
        $perPage = config('app.pagination_per_page');
        $query = Volunteering::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->orderBy('status','desc') // status 1 first
    ->orderBy('start_datetime', 'desc');
        $eloquentVolunteerings = Volunteering::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->orderBy('status','desc') // status 1 first
        ->orderBy('start_datetime', 'desc')->paginate($perPage);
        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        activityLog('view volunteering in specific date',$query->first(),'The user viewed volunteering in specific date '.$date['date'],'view');

        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }

    public function createInterestVolunteering($slug)
    {
        $user = Auth::guard('api')->user();
        $volunteering =Volunteering::findBySlug($slug);
        $volunteeringId =$volunteering?->id;
        ActivityLog('volunteering',$volunteering,'the user interested in the volunteering','interest');

        $user->volunteeringInterestables()->attach([$volunteeringId]);

        //add points and streak
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }
    public function disinterestVolunteering($slug)
    {
        $user = Auth::guard('api')->user();
        $volunteering =Volunteering::findBySlug($slug);
        $volunteeringId =$volunteering->id;
        $user->volunteeringInterestables()->detach($volunteeringId);
        ActivityLog('volunteering',$volunteering,'the user disinterest in the volunteering','disinterest');
        $user->deductPoints(10);
    }

    public function search($query)
    {
        $perPage =  config('app.pagination_per_page');

        $eloquentVolunteerings = Volunteering::where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('name_en', 'like', '%' . $query . '%')
                ->orWhere('name_ar', 'like', '%' . $query . '%');
        })->paginate($perPage);

        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        if($query) {
            activityLog('search for specific volunteering', $eloquentVolunteerings->first(), $query, 'search');
        }
        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }

    public function interestedList($id)
    {
        $perPage = config('app.pagination_per_page');
        $query = Volunteering::whereHas('interestedUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->paginate($perPage);
        $eloquentVolunteerings = Volunteering::whereHas('interestedUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->paginate($perPage);

        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        activityLog('show his interested list for volunteering',$query->first(), 'the user view his volunteering interest list','view');
        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }
}
