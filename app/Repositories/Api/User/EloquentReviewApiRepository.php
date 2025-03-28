<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\ReviewResource;
use App\Interfaces\Gateways\Api\User\ReviewApiRepositoryInterface;
use App\Models\Reviewable;
use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use App\Notifications\Users\review\NewReviewNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use LevelUp\Experience\Models\Activity;

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
        DB::beginTransaction();

        try {
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

            $reviewPivotTable = $user->{$relationship}()->getTable(); // e.g., 'review_trip'
            $reviewType = get_class($reviewItem); // e.g., App\Models\Trip

            $reviewPivotId = DB::table($reviewPivotTable)
                ->where('user_id', $user->id)
                ->where('reviewable_id', $reviewItem->id)
                ->where('reviewable_type', $reviewType)
                ->latest('created_at') // Make sure this column exists
                ->value('id');

            if (in_array($data['type'], ['trip', 'guideTrip'])) {
                $userPost = $reviewItem->user;
                $ownerToken = $userPost->DeviceToken->token;
                $receiverLanguage = $userPost->lang;

                $dataBaseNotification = [
                    'title_en'     => Lang::get('app.notifications.new-review', [], 'en'),
                    'title_ar'     => Lang::get('app.notifications.new-review', [], 'ar'),
                    'body_en'      => Lang::get('app.notifications.new-user-review-in-' . $data['type'], [
                        'username' => Auth::guard('api')->user()->username
                    ], 'en'),
                    'body_ar'      => Lang::get('app.notifications.new-user-review-in-' . $data['type'], [
                        'username' => Auth::guard('api')->user()->username
                    ], 'ar'),

                    'type'         => 'review_' . $data['type'],
                    'slug'         => $data['slug'],
                    'review_id'    => $reviewPivotId
                ];

                $title = Lang::get('app.notifications.new-review', [], $receiverLanguage);
                $body = Lang::get('app.notifications.new-user-review-in-' . $data['type'], [
                    'username' => Auth::guard('api')->user()->username
                ], $receiverLanguage);
                $firebaseNotification = [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => asset('assets/icon/speaker.png'),
                    'sound' => 'default',
                ];

                Notification::send($userPost, new NewReviewNotification($dataBaseNotification));
                sendNotification([$ownerToken], $firebaseNotification);
            }

            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            activityLog('review', $reviewItem, 'the user add new review', 'create');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e; // Or handle the error accordingly
        }
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
        activityLog('review', $reviewItem, 'the user updated review', 'update');
    }

    public function deleteReview($data)
    {
        $user = Auth::guard('api')->user();
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $reviewItem = $modelClass::findBySlug($data['slug']);
        Reviewable::where('user_id', $user?->id)->where('reviewable_type', $modelClass)->where('reviewable_id', $reviewItem?->id)->delete();

        activityLog('review', $reviewItem, 'the user delete review', 'delete');
        $user->deductPoints(10);
    }

    public function reviewsLike($data)
    {
        DB::beginTransaction();

        try {
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
                            'body'  => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                            'icon' => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ];
                        Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user(), $review));
                    } else {
                        $notificationData = [
                            'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                            'icon'  => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ];
                        Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user(), $review));
                    }
                } else {
                    $review->like()->detach(Auth::guard('api')->user()->id);
                }
            } else {
                $review->like()->attach(Auth::guard('api')->user()->id, ['status' => $status]);
                if ($data['status'] == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                        'body'  => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'icon'  => asset('assets/icon/speaker.png'),
                        'sound' => 'default',
                    ];
                    Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user(), $review));
                } else {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                        'body'  => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'icon'  => asset('assets/icon/speaker.png'),
                        'sound' => 'default',
                    ];
                    Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user(), $review));
                }
            }

            if (!empty($notificationData)) {
                sendNotification([$ownerToken], $notificationData);
            }
            activityLog($data['status'], $review, 'the user ' . $data['status'] . ' review', $data['status']);

            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
