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
use Illuminate\Notifications\DatabaseNotification;

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

            if (in_array($data['type'], ['trip', 'guideTrip', 'service'])) {

                $userPost = $data['type'] == 'service' ? $reviewItem->provider : $reviewItem->user;

                if ($userPost->id !== $user->id) {
                    $tokens = $userPost->DeviceTokenMany->pluck('token')->toArray();
                    $receiverLanguage = $userPost->lang;

                    $dataBaseNotification = [
                        'title_en'     => Lang::get('app.notifications.new-review', [], 'en'),
                        'title_ar'     => Lang::get('app.notifications.new-review', [], 'ar'),
                        'body_en'      => Lang::get('app.notifications.new-user-review-in-' . $data['type'], [
                            'username' => $user->username
                        ], 'en'),
                        'body_ar'      => Lang::get('app.notifications.new-user-review-in-' . $data['type'], [
                            'username' => $user->username
                        ], 'ar'),

                        'type'         => 'review_' . $data['type'],
                        'slug'         => $data['slug'],
                        'review_id'    => $reviewPivotId
                    ];

                    $title = Lang::get('app.notifications.new-review', [], $receiverLanguage);
                    $body = Lang::get('app.notifications.new-user-review-in-' . $data['type'], ['username' => $user->username], $receiverLanguage);
                    $firebaseNotification = [
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                            'sound' => 'default'
                        ],
                        'data' => [
                            'type'        => 'review_' . $data['type'],
                            'slug'        => $data['slug'],
                            'review_id'   => $reviewPivotId
                        ]
                    ];

                    Notification::send($userPost, new NewReviewNotification($dataBaseNotification));

                    if (!empty($tokens))
                        sendNotification($tokens, $firebaseNotification);
                }
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

        // Get review ID before deleting
        $reviewId = $reviewItem->reviews()->where('user_id', $user->id)->value('id');

        // Delete the review
        Reviewable::where('user_id', $user->id)
            ->where('reviewable_type', $modelClass)
            ->where('reviewable_id', $reviewItem->id)
            ->delete();

        // Delete the notification
        if ($reviewId) {
            DatabaseNotification::where('type', 'App\Notifications\Users\review\NewReviewNotification')
                ->whereJsonContains('data->options->review_id', $reviewId)
                ->where('notifiable_id', $reviewItem->provider_id)
                ->delete();
        }
        activityLog('review', $reviewItem, 'the user delete review', 'delete');
        $user->deductPoints(10);
    }

    public function reviewsLike($data)
    {
        DB::beginTransaction();

        try {
            $review = Reviewable::findOrFail($data['review_id']);
            $authUser = Auth::guard('api')->user();
            $status = $data['status'] === 'like' ? '1' : '0';
            $userReview = $review->user;
            $isSelfReview = $review->user_id === $authUser->id;
            $receiverLang = $userReview->lang;
            $tokens = $userReview->DeviceTokenMany->pluck('token')->toArray();

            $existingLike = $review->like()->where('user_id', $authUser->id)->first();

            if ($existingLike) {
                $previousStatus = $existingLike->pivot->status;

                if ($previousStatus == $status) {
                    // Case 1 or 3 - Removing existing like/dislike
                    $review->like()->detach($authUser->id);

                    // Delete old notification
                    $this->deleteReviewNotification($userReview->id, $review->id, $previousStatus);

                    DB::commit();
                    return;
                }

                // Case 2 - Switch between like and dislike
                $review->like()->updateExistingPivot($authUser->id, ['status' => $status]);
                $this->deleteReviewNotification($userReview->id, $review->id, $previousStatus);
            } else {
                // First-time like or dislike
                $review->like()->attach($authUser->id, ['status' => $status]);
                $authUser->addPoints(10);
                $authUser->recordStreak(Activity::find(1));
            }

            if (!$isSelfReview) {
                if ($status === '1') {
                    Notification::send($userReview, new NewReviewLikeNotification($authUser, $review));
                } else {
                    Notification::send($userReview, new NewReviewDisLikeNotification($authUser, $review));
                }
            }

            $notificationData = [
                'notification' => [
                    'title' => Lang::get("app.notifications.new-review-{$data['status']}", [], $receiverLang),
                    'body'  => Lang::get("app.notifications.new-user-{$data['status']}-in-review", ['username' => $authUser->username], $receiverLang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default'
                ],
                'data' => [
                    'type' => 'review_' . lcfirst(class_basename($review->reviewable_type)),
                    'slug' =>  $review->reviewable->slug,
                    'review_id' => $review->id
                ]
            ];

            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            activityLog($data['status'], $review, 'the user ' . $data['status'] . ' review', $data['status']);


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function deleteReviewNotification($userId, $reviewId, $status)
    {
        $type = (string) $status === '1' ? NewReviewLikeNotification::class : NewReviewDisLikeNotification::class;
        if ($type) {
            DatabaseNotification::where('notifiable_id', $userId)
                ->where('type', $type)
                ->where('data->options->review_id', $reviewId)
                ->delete();
        }
    }
}
