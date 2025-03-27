<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\EventResource;
use App\Http\Resources\SingleEventResource;
use App\Interfaces\Gateways\Api\User\EventApiRepositoryInterface;
use App\Models\Event;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use LevelUp\Experience\Models\Activity;


class EloquentEventApiRepository implements EventApiRepositoryInterface
{
    public function getAllEvents()
    {
        $perPage = config('app.pagination_per_page');
        $query= Event::OrderBy('start_datetime');
        $eloquentEvents = Event::OrderBy('start_datetime')->paginate($perPage);
        $eventsArray = $eloquentEvents->toArray();

        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];


        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => EventResource::collection($eloquentEvents),
            'pagination' => $pagination
        ];
    }

    public function activeEvents()
    {
        $perPage = config('app.pagination_per_page');
        //we need cron job for update the status of event
        $now = now()->setTimezone('Asia/Riyadh');
        //retrieve active event
        $query = Event::orderBy('start_datetime')->where('status', '1')->where('end_datetime', '>=', $now);
        $eloquentEvents = Event::orderBy('start_datetime')->where('status', '1')->where('end_datetime', '>=', $now)->paginate($perPage);
        //update the event where it inactive
        Event::where('status', '1')->whereNotIn('id', $eloquentEvents->pluck('id'))->update(['status' => '0']);

        $eventsArray = $eloquentEvents->toArray();
        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];


        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => EventResource::collection($eloquentEvents),
            'pagination' => $pagination
        ];
    }

    public function event($slug)
    {
        $eloquentEvents = Event::where('slug', $slug)->first();
        activityLog('event',$eloquentEvents,'The user viewed event','view');
        return new SingleEventResource($eloquentEvents);
    }

    public function dateEvents($date)
    {
        $perPage = config('app.pagination_per_page');
        $query = Event::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->where('status', '1');
        $eloquentEvents = Event::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->where('status', '1')->paginate($perPage);
        $eventsArray = $eloquentEvents->toArray();
        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];

        activityLog('event',$query->first(),'The user viewed event in specific date '.$date['date'],'view');

        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => new ResourceCollection(EventResource::collection($eloquentEvents)),
            'pagination' => $pagination
        ];
    }

    public function createInterestEvent($slug)
    {
        $user = Auth::guard('api')->user();
        $event= Event::findBySlug($slug);
        $eventId = $event?->id;
        $user->eventInterestables()->attach([$eventId]);
        ActivityLog('event',$event,'the user interested in the event','interest');

        //add points and streak
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function disinterestEvent($slug)
    {
        $user = Auth::guard('api')->user();
        $event= Event::findBySlug($slug);
        $eventId = $event?->id;
        $user->eventInterestables()->detach($eventId);
        ActivityLog('event',$event,'the user disinterest in the event','disinterest');
        $user->deductPoints(10);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');
        $eloquentEvents = Event::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })->paginate($perPage);

        $eventsArray = $eloquentEvents->toArray();
        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];
        activityLog('event',$eloquentEvents->first(), $query,'search');
        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => EventResource::collection($eloquentEvents),
            'pagination' => $pagination
        ];
    }

    public function interestList($id)
    {
        $perPage = config('app.pagination_per_page');
        $query = Event::whereHas('interestedUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        });
        $eloquentEvents = Event::whereHas('interestedUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->paginate($perPage);

        $eventsArray = $eloquentEvents->toArray();

        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];
        activityLog('event',$query->first(), 'the user view his events interest list','view');
        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => EventResource::collection($eloquentEvents),
            'pagination' => $pagination
        ];
    }
}
