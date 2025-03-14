<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\FeaturesResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserFavoriteResource;
use App\Http\Resources\UserFavoriteSearchResource;
use App\Interfaces\Gateways\Api\User\FavoriteApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ReviewApiRepositoryInterface;
use App\Models\Feature;
use App\Models\Reviewable;
use App\Models\User;
use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;

class EloquentReviewApiRepository implements ReviewApiRepositoryInterface
{

    public function allReviews($data)
    {
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $record = $modelClass::findBySlug($data['slug']);
        return ReviewResource::collection($record->reviews);
    }

    public function addReview($data)
    {
        $user = Auth::guard('api')->user();

        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $reviewItem = $modelClass::findBySlug($data['slug']);

        $relationship = 'review' . ucfirst($data['type']);
        if (!method_exists($user, $relationship)) {
            throw new \Exception(__("validation.api.relationship_not_exist", ['relationship' => $relationship]));
        }
        $filteredContent = app(Pipeline::class)
            ->send($data['comment'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['comment'] = $filteredContent;

        $user->{$relationship}()->attach($reviewItem?->id, [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);

    }

    public function updateReview($data)
    {
        $user = Auth::guard('api')->user();

        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $reviewItem = $modelClass::findBySlug($data['slug']);

        $filteredContent = app(Pipeline::class)
            ->send($data['comment'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['comment'] = $filteredContent;
        $existingReview = Reviewable::where('user_id', $user?->id)->where('reviewable_type', $modelClass)->where('reviewable_id', $reviewItem?->id)->first();

        $existingReview->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

    }

    public function deleteReview($data)
    {
        $user = Auth::guard('api')->user();
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $reviewItem = $modelClass::findBySlug($data['slug']);
        Reviewable::where('user_id', $user?->id)->where('reviewable_type', $modelClass)->where('reviewable_id', $reviewItem?->id)->delete();
    }

    public function reviewsLike($data)
    {
        $review = Reviewable::find($data['review_id']);
        $status = $data['status'] == "like" ? '1' : '0';
        $userReview = $review->user;
        $receiverLanguage = $userReview->lang;
        $ownerToken = $userReview->DeviceToken->token;
        $notificationData = [];

        $existingLike = $review->like()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->pivot->status != $status) {
                $review->like()->updateExistingPivot(Auth::guard('api')->user()->id, ['status' => $status]);
                if ($data['status'] == "like") {
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
            if ($data['status'] == "like") {
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
        if (!empty($notificationData)) {
            sendNotification($ownerToken, $notificationData);
        }
    }

}
