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


class EloquentVolunteeringApiRepository implements VolunteeringApiRepositoryInterface
{
    public function getAllVolunteerings()
    {
        $perPage = 15;
        $eloquentVolunteerings = Volunteering::OrderBy('start_datetime', 'desc')->paginate($perPage);

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
        $perPage = 15;
        $now = now()->setTimezone('Asia/Riyadh');
        $eloquentVolunteerings = Volunteering::orderBy('start_datetime')->where('status', '1')->where('end_datetime', '>=', $now)->paginate($perPage);
        //edit the status should has cron job
        Volunteering::where('status', '1')->whereNotIn('id', $eloquentVolunteerings->pluck('id'))->update(['status' => '0']);
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
        activityLog('volunteering',$eloquentVolunteerings,'The user viewed volunteering','view');
        return new SingleVolunteeringResource($eloquentVolunteerings);
    }

    public function dateVolunteerings($date)
    {
        $perPage = 15;
        $eloquentVolunteerings = Volunteering::whereDate('start_datetime', '<=', $date)->whereDate('end_datetime', '>=', $date)->where('status', '1')->paginate($perPage);
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

    public function createInterestVolunteering($data)
    {
        $user = User::find($data['user_id']);
        $user->volunteeringInterestables()->attach([$data['volunteering_id']]);
    }
    public function disinterestVolunteering($id)
    {
        $user = Auth::guard('api')->user();
        $user->volunteeringInterestables()->detach($id);
    }
    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteVolunteering()->attach($id);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteVolunteering()->detach($id);
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
        $user->reviewVolunteering()->attach($data['volunteering_id'], [
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
        $user->reviewVolunteering()->sync([$data['volunteering_id'] => [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]]);
    }

    public function deleteReview($id)
    {
        $user = Auth::guard('api')->user();
        $user->reviewVolunteering()->detach($id);
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

        $eloquentVolunteerings = Volunteering::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })->paginate($perPage);

        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];
        activityLog('volunteering',$eloquentVolunteerings->first(),$query,'search');

        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => VolunteeringResource::collection($eloquentVolunteerings),
            'pagination' => $pagination
        ];
    }

    public function interestedList($userId)
    {
        $perPage = 10;

        $eloquentVolunteerings = Volunteering::whereHas('interestedUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->paginate($perPage);

        $volunteeringArray = $eloquentVolunteerings->toArray();

        $pagination = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['next_page_url'],
            'total' => $volunteeringArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'volunteering' => new ResourceCollection(VolunteeringResource::collection($eloquentVolunteerings)),
            'pagination' => $pagination
        ];
    }
}
