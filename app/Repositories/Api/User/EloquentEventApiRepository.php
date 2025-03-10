<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\SingleEventResource;
use App\Interfaces\Gateways\Api\User\EventApiRepositoryInterface;
use App\Models\Category;
use App\Models\Event;
use App\Models\Reviewable;
use App\Models\User;

use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentEventApiRepository implements EventApiRepositoryInterface
{
    public function getAllEvents()
    {
        $perPage = 15;
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
        $perPage = 15;
        //we need cron job for update the status of event
        $now = now()->setTimezone('Asia/Riyadh');
        //retrieve active event
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
        $perPage = 15;
        $eloquentEvents = Event::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->where('status', '1')->paginate($perPage);
        $eventsArray = $eloquentEvents->toArray();
        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => new ResourceCollection(EventResource::collection($eloquentEvents)),
            'pagination' => $pagination
        ];
    }

    public function createInterestEvent($data)
    {
        $user = User::find($data['user_id']);
        $user->eventInterestables()->attach([$data['event_id']]);
    }

    public function disinterestEvent($id)
    {
        $user = Auth::guard('api')->user();
        $user->eventInterestables()->detach($id);
    }

    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteEvent()->attach($id);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteEvent()->detach($id);
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
        $user->reviewEvent()->attach($data['event_id'], [
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
        $user->reviewEvent()->sync([$data['event_id'] => [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]]);
    }

    public function deleteReview($id)
    {
        $user = Auth::guard('api')->user();
        $user->reviewEvent()->detach($id);
    }

    public function reviewsLike($request)
    {
        $review = Reviewable::find($request->review_id);
        $status = $request->status == "like" ? '1' : '0';
        $userReview = $review->user;
        $receiverLanguage = $userReview->lang;
        $ownerToken = $userReview->DeviceToken->token;
        $notificationData = [];

        $existingLike = $review->like()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->pivot->status != $status) {
                $review->like()->updateExistingPivot(Auth::guard('api')->user()->id, ['status' => $status]);
                if ($request->status == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));
                } else {
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
            if ($request->status == "like") {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));
            } else {
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
        $perPage = 15;
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
        $perPage = 15;

        $eloquentEvents = Event::whereHas('interestedUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->paginate($perPage);

        $eventsArray = $eloquentEvents->toArray();

        $pagination = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['next_page_url'],
            'total' => $eventsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => new ResourceCollection(EventResource::collection($eloquentEvents)),
            'pagination' => $pagination
        ];
    }
}
