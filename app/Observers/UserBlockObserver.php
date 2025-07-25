<?php

namespace App\Observers;

use App\Models\Reviewable;
use App\Models\ReviewLike;
use App\Models\UserBlock;

class UserBlockObserver
{
    /**
     * Handle the UserBlock "created" event.
     */
    public function created(UserBlock $userBlock): void
    {
        // ONE: UNFOLLOW
        $blocker = $userBlock->blocker;
        $blocked = $userBlock->blocked;

        $blocker->following()->detach($blocked->id);
        $blocked->following()->detach($blocker->id);

        // TWO: UNLIKE REVIEWS
        $blockedReviewIds = Reviewable::where('user_id', $blocked->id)->pluck('id');
        ReviewLike::where('user_id', $blocker->id)->whereIn('review_id', $blockedReviewIds)->delete();

        $blockerReviewIds = Reviewable::where('user_id', $blocker->id)->pluck('id');
        ReviewLike::where('user_id', $blocked->id)->whereIn('review_id', $blockerReviewIds)->delete();

        // THREE: DELETE GUIDE RATING
        $blockerRating = $blocker->userGuideRating()->where('guide_id', $blocked->id)->first();
        if ($blockerRating) {
            $blockerRating->delete();
        }
    }

    /**
     * Handle the UserBlock "deleted" event.
     */
    public function deleted(UserBlock $userBlock): void
    {
        //
    }
}
