<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\PlaceApiController;
use App\Http\Controllers\Api\User\TripApiController;
use App\Http\Controllers\Api\User\EventApiController;
use App\Http\Controllers\Api\User\VolunteeringApiController;
use App\Http\Controllers\Api\User\PlanApiController;
use App\Http\Controllers\Api\User\PostApiController;
use App\Http\Controllers\Api\User\AuthUser\AuthUserController;
use App\Http\Controllers\Api\User\FollowApiController;
use App\Http\Controllers\Api\User\CommentApiController;
use App\Http\Controllers\Api\User\ReplyApiController;
use App\Http\Controllers\Api\User\GameApiController;
use App\Http\Controllers\Api\User\GuideTripApiController;
use App\Http\Controllers\Api\User\GuideTripUserApiController;
use App\Http\Controllers\Api\User\GuideRatingController;
use App\Http\Controllers\Api\User\GroupChatController;
use App\Http\Controllers\Api\User\FavoriteApiController;
use App\Http\Controllers\Api\User\ReviewApiController;
use Illuminate\Support\Facades\Broadcast;


Route::middleware(['firstLogin'])->group(function () {
    ///////////////////////////////////////////start review//////////////////////////////////////////////////////////
    Route::get('/user/profile', [UserProfileController::class, 'userDetails'])->name('user.profile');
    Route::get('other/user/profile/{slug}', [UserProfileController::class, 'otherUserProfile'])->name('other.user.profile');

    //favorite system
    Route::post('favorite/{type}/{slug}', [FavoriteApiController::class, 'favorite']);
    Route::delete('favorite/{type}/{slug}/delete', [FavoriteApiController::class, 'unfavored']);
    Route::get('user/all/favorite', [FavoriteApiController::class, 'allUserFavorites']);
    //need a lot of work to search on trip and guide trip of active owner in search
    Route::get('user/favorite/search', [FavoriteApiController::class, 'favSearch']);

    //review system
    Route::group(['prefix' => 'review'], function () {
        Route::get('all/{type}/{slug}', [ReviewApiController::class, 'reviews']);
        Route::post('add/{type}/{slug}', [ReviewApiController::class, 'addReview']);
        Route::put('update/{type}/{slug}', [ReviewApiController::class, 'updateReview']);
        Route::delete('delete/{type}/{slug}', [ReviewApiController::class, 'deleteReview']);
        Route::post('{status}/{review_id}', [ReviewApiController::class, 'likeDislike']);
    });

    Route::post('visited/place/{slug}', [PlaceApiController::class, 'createVisitedPlace']);
    Route::delete('visited/place/{slug}/delete', [PlaceApiController::class, 'deleteVisitedPlace']);

    // All Routes For event
    Route::group(['prefix' => 'event'], function () {
        Route::get('/interested/list', [EventApiController::class, 'interestList']);
        Route::post('/interest/{slug}', [EventApiController::class, 'interest']);
        Route::delete('/disinterest/{slug}', [EventApiController::class, 'disinterest']);

    });

    // All Routes For event
    Route::group(['prefix' => 'volunteering'], function () {
        Route::get('/interested/list', [VolunteeringApiController::class, 'interestedList']);
        Route::post('/interest/{slug}', [VolunteeringApiController::class, 'interest']);
        Route::delete('/disinterest/{slug}', [VolunteeringApiController::class, 'disinterest']);

    });
// we need to concentrate about the previous (notification / activity log)
    /*************/
    Route::prefix('post')->group(function () {
        Route::get('/followings', [PostApiController::class, 'followingPost']);
        Route::post('/store', [PostApiController::class, 'store']);
        //we should care about the method of post to become put .....
        Route::post('/update/{post_id}', [PostApiController::class, 'update']);
        Route::get('show/{post_id}', [PostApiController::class, 'show']);
        Route::delete('/image/delete/{media_id}', [PostApiController::class, 'DeleteImage']);
        Route::delete('/delete/{post_id}', [PostApiController::class, 'destroy']);

        Route::post('favorite/{post_id}', [PostApiController::class, 'createFavoritePost']);
        Route::delete('favorite/{post_id}/delete', [PostApiController::class, 'deleteFavoritePost']);
        Route::post('/like-dislike/{status}/{post_id}', [PostApiController::class, 'likeDislike']);

        //comment system
        Route::post('/comment/store', [CommentApiController::class, 'commentStore']);
        Route::put('/comment/update/{comment_id}', [CommentApiController::class, 'commentUpdate']);
        Route::delete('/comment/delete/{comment_id}', [CommentApiController::class, 'commentDelete']);
        Route::post('comment/like-dislike/{status}/{type_id}', [CommentApiController::class, 'likeDislike']);

    });

    Route::group(['prefix' => 'guide'], function () {
        Route::post('/trips/store', [GuideTripApiController::class, 'store']);
        Route::post('/trips/update/{slug}', [GuideTripApiController::class, 'update']);
        Route::delete('/trips/delete/{slug}', [GuideTripApiController::class, 'delete']);
        Route::delete('/image/delete/{media_id}', [GuideTripApiController::class, 'DeleteImage']);
        Route::get('join/requests/list/{slug}',[GuideTripApiController::class, 'joinRequests']);
        Route::put('change/join/request/{status}/{guide_trip_user_id}',[GuideTripApiController::class, 'changeJoinRequestStatus']);
    });

    Route::group(['prefix' => 'user/guide-trip'], function () {
        Route::get('/subscription/{guide_trip_slug}', [GuideTripUserApiController::class, 'allSubscription']);
        Route::post('/store/{guide_trip_slug}', [GuideTripUserApiController::class, 'store']);
        Route::put('/update/{guide_trip_slug}', [GuideTripUserApiController::class, 'update']);
        Route::delete('/delete/{guide_trip_slug}', [GuideTripUserApiController::class, 'delete']);

    });

    Route::controller(GuideRatingController::class)->group(function () {
        Route::get('rating/guide/show/{guide_slug}', 'show');
        Route::post('rating/guide/store/{guide_slug}', 'create');
        Route::post('rating/guide/update/{guide_slug}', 'update');
        Route::delete('rating/guide/delete/{guide_slug}', 'delete');
    });

    Route::group(['prefix' => 'follow'], function () {
        Route::get('/followers/requests', [FollowApiController::class, 'followersRequest']);
        Route::post('/create/{following_slug}', [FollowApiController::class, 'follow']);
        Route::delete('/delete/{following_slug}', [FollowApiController::class, 'unfollow']);
        Route::get('/followers/{user_slug}', [FollowApiController::class, 'followers']);
        Route::get('/followings/{user_slug}', [FollowApiController::class, 'followings']);
        Route::put('/accept/following-request/{follower_slug}', [FollowApiController::class, 'acceptFollowerRequest']);
        Route::put('/unaccepted/following-request/{follower_slug}', [FollowApiController::class, 'UnacceptedFollowerRequest']);
    });



    ////////////////////////////////////////////end review////////////////////////////////////////////////////////////

    Route::group(['prefix' => 'chat'], function () {
        Route::get('/{conversation_id?}', [GroupChatController::class, 'messages']);
        Route::get('members/{conversation_id?}', [GroupChatController::class, 'members']);
        Route::post('/store', [GroupChatController::class, 'store']);
    });
    // All Routes For Trip
    Route::group(['prefix' => 'trip'], function () {
        Route::get('/tags', [TripApiController::class, 'tags']);
        Route::get('/', [TripApiController::class, 'index']);
        Route::post('/create', [TripApiController::class, 'create']);

        // Private Trips
        Route::get('/private', [TripApiController::class, 'privateTrips']);
        Route::post('/user/{status?}', [TripApiController::class, 'acceptCancel']);

        // Invitations Trips
        Route::get('/invitations', [TripApiController::class, 'invitationTrips']);

        // Liking or Disliking a Review
        Route::get('review/{status?}/{review_id?}', [TripApiController::class, 'likeDislike']);

        // Middleware Grouped Routes
        Route::middleware('CheckTripStatus')->group(function () {
            // Joining Trips
            Route::post('/join/{trip_id?}', [TripApiController::class, 'join']);
            Route::delete('/join/cancel/{trip_id?}', [TripApiController::class, 'cancelJoin']);
            // Trip Details
            Route::get('/details/{trip_id?}', [TripApiController::class, 'tripDetails']);

            // Managing Reviews
            Route::post('add/review/{trip_id?}', [TripApiController::class, 'addReview']);
            Route::post('update/review/{trip_id?}', [TripApiController::class, 'updateReview']);
            Route::delete('delete/review/{trip_id?}', [TripApiController::class, 'deleteReview']);
            Route::get('all/reviews/{trip_id?}', [TripApiController::class, 'reviews']);
            // Removing and Updating Trips
            Route::delete('/delete/{trip_id?}', [TripApiController::class, 'remove']);
            Route::post('/update/{trip_id?}', [TripApiController::class, 'update']);
            // Invitations Trips Accept or Cancel
            Route::post('/invitation-status/{status?}', [TripApiController::class, 'acceptCancelInvitation']);
            // Remove User From Trip After User Joined
            Route::post('/remove-user/{trip_id?}', [TripApiController::class, 'removeUser']);
        });
    });
    // All Routes For Plan
    Route::group(['prefix' => 'plan'], function () {
        Route::get('/', [PlanApiController::class, 'index']);
        Route::post('/create', [PlanApiController::class, 'create']);
        Route::post('/update', [PlanApiController::class, 'update']);
        Route::delete('/{plan_id?}/delete', [PlanApiController::class, 'destroy']);
        Route::get('/show/{plan_id?}', [PlanApiController::class, 'show']);
        Route::get('/my-plans', [PlanApiController::class, 'myPlans']);
    });

    Route::group(['prefix' => 'game'], function () {
        Route::get('/start', [GameApiController::class, 'start']);
        Route::post('/next-question', [GameApiController::class, 'next']);
        Route::post('/finish', [GameApiController::class, 'finish']);
    });

});

// All Routes For profile
Route::post('profile/update', [UserProfileController::class, 'update']);
Route::get('all/tags', [UserProfileController::class, 'allTags']);

//we will see to add it or not
Route::post('user/set-location', [UserProfileController::class, 'setLocation']);

//delete and deactivate account need time
Route::post('delete/account', [AuthUserController::class, 'deleteAccount']);
Route::get('user/deactivate-account', [AuthUserController::class, 'deactivateAccount']);

Broadcast::routes();
