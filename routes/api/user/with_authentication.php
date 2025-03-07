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
use Illuminate\Support\Facades\Broadcast;


Route::middleware(['firstLogin'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'userDetails'])->name('user.profile');
    Route::get('other/user/profile/{id}', [UserProfileController::class, 'otherUserProfile'])->name('other.user.profile');
    Route::post('favorite/place/{place_id?}', [PlaceApiController::class, 'createFavoritePlace']);
    Route::delete('favorite/place/{place_id?}/delete', [PlaceApiController::class, 'deleteFavoritePlace']);
    Route::post('visited/place/{place_id?}', [PlaceApiController::class, 'createVisitedPlace']);
    Route::delete('visited/place/{place_id?}/delete', [PlaceApiController::class, 'deleteVisitedPlace']);

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
            // Managing Favorites
            Route::post('/favorite/{trip_id?}', [TripApiController::class, 'favorite']);
            Route::delete('/favorite/{trip_id?}/delete', [TripApiController::class, 'deleteFavorite']);
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

    // All Routes For event
    Route::group(['prefix' => 'event'], function () {
        Route::get('/interested/list', [EventApiController::class, 'interestList']);
        Route::post('/interest/{event_id?}', [EventApiController::class, 'interest']);
        Route::delete('/disinterest/{event_id?}', [EventApiController::class, 'disinterest']);
        Route::post('/favorite/{event_id?}', [EventApiController::class, 'favorite']);
        Route::delete('/favorite/{event_id?}/delete', [EventApiController::class, 'deleteFavorite']);

        Route::post('add/review/{event_id?}', [EventApiController::class, 'addReview']);
        Route::post('update/review/{event_id?}', [EventApiController::class, 'updateReview']);
        Route::delete('delete/review/{event_id?}', [EventApiController::class, 'deleteReview']);
        Route::post('review/{status?}/{review_id?}', [EventApiController::class, 'likeDislike']);
    });

    // All Routes For event
    Route::group(['prefix' => 'volunteering'], function () {
        Route::get('/interested/list', [VolunteeringApiController::class, 'interestedList']);
        Route::post('/interest/{volunteering_id?}', [VolunteeringApiController::class, 'interest']);
        Route::delete('/disinterest/{volunteering_id?}', [VolunteeringApiController::class, 'disinterest']);
        Route::post('/favorite/{volunteering_id?}', [VolunteeringApiController::class, 'favorite']);
        Route::delete('/favorite/{volunteering_id?}/delete', [VolunteeringApiController::class, 'deleteFavorite']);

        Route::post('add/review/{volunteering_id?}', [VolunteeringApiController::class, 'addReview']);
        Route::post('update/review/{volunteering_id?}', [VolunteeringApiController::class, 'updateReview']);
        Route::delete('delete/review/{volunteering_id?}', [VolunteeringApiController::class, 'deleteReview']);
        Route::post('review/{status?}/{review_id?}', [VolunteeringApiController::class, 'likeDislike']);
    });

    // All Routes For Plan
    Route::group(['prefix' => 'plan'], function () {
        Route::get('/', [PlanApiController::class, 'index']);
        Route::post('/create', [PlanApiController::class, 'create']);
        Route::post('/update', [PlanApiController::class, 'update']);
        Route::delete('/{plan_id?}/delete', [PlanApiController::class, 'destroy']);
        Route::get('/show/{plan_id?}', [PlanApiController::class, 'show']);
        Route::post('favorite/{plan_id?}', [PlanApiController::class, 'createFavoritePlan']);
        Route::delete('favorite/{plan_id?}/delete', [PlanApiController::class, 'deleteFavoritePlan']);
        Route::get('/my-plans', [PlanApiController::class, 'myPlans']);
    });

    //    // All Routes For Plan
    //    Route::group(['prefix' => 'post'], function () {
    //        Route::get('/', [PostApiController::class, 'index']);
    //        Route::post('/store', [PostApiController::class, 'store']);
    //        Route::post('/update/{post_id}', [PostApiController::class, 'update']);
    //        Route::get('show/{post_id}', [PostApiController::class, 'show']);
    //        Route::delete('/image/delete/{media_id}', [PostApiController::class, 'DeleteImage']);
    //        Route::delete('/delete/{post_id}', [PostApiController::class, 'destroy']);
    //        Route::post('favorite/{post_id?}', [PostApiController::class, 'createFavoritePost']);
    //        Route::delete('favorite/{post_id?}/delete', [PostApiController::class, 'deleteFavoritePost']);
    //    });

    // All Routes For Review Place
    Route::group(['prefix' => 'place'], function () {
        Route::post('add/review/{place_id?}', [PlaceApiController::class, 'addReview']);
        Route::post('update/review/{place_id?}', [PlaceApiController::class, 'updateReview']);
        Route::delete('delete/review/{place_id?}', [PlaceApiController::class, 'deleteReview']);
        Route::post('review/{status?}/{review_id?}', [PlaceApiController::class, 'likeDislike']);
    });

    Route::prefix('post')->group(function () {
        Route::get('/followings', [PostApiController::class, 'followingPost']);
        Route::post('/store', [PostApiController::class, 'store']);
        Route::post('/update', [PostApiController::class, 'update']);
        Route::get('show/{post_id}', [PostApiController::class, 'show']);
        Route::delete('/image/delete/{media_id}', [PostApiController::class, 'DeleteImage']);
        Route::delete('/delete/{post_id}', [PostApiController::class, 'destroy']);
        Route::post('favorite/{post_id?}', [PostApiController::class, 'createFavoritePost']);
        Route::delete('favorite/{post_id?}/delete', [PostApiController::class, 'deleteFavoritePost']);
        // Route::post('{status?}/{post_id?}', [PostApiController::class, 'likeDislike']);
        Route::post('/like-dislike/{status?}/{post_id?}', [PostApiController::class, 'likeDislike']);


        Route::post('/comment/store', [CommentApiController::class, 'commentStore']);
        Route::post('/comment/update/{comment_id?}', [CommentApiController::class, 'commentUpdate']);
        Route::delete('/comment/delete/{comment_id?}', [CommentApiController::class, 'commentDelete']);
        // Route::post('comment/{status?}/{comment_id?}', [CommentApiController::class, 'likeDislike']);
        Route::post('comment/like-dislike/{status?}/{comment_id?}', [CommentApiController::class, 'likeDislike']);

        Route::post('reply/create', [ReplyApiController::class, 'replyStore']);
        Route::post('reply/update/{reply_id?}', [ReplyApiController::class, 'replyUpdate']);
        Route::delete('reply/delete/{reply_id?}', [ReplyApiController::class, 'replyDelete']);
        Route::post('reply/{status?}/{reply_id?}', [ReplyApiController::class, 'likeDislike']);
    });

    Route::group(['prefix' => 'follow'], function () {
        Route::get('/followers/requests', [FollowApiController::class, 'followersRequest']);
        Route::post('/create', [FollowApiController::class, 'follow']);
        Route::delete('/delete/{following_id?}', [FollowApiController::class, 'unfollow']);
        Route::get('/followers/{user_id?}', [FollowApiController::class, 'followers']);
        Route::get('/followings/{user_id?}', [FollowApiController::class, 'followings']);
        Route::post('/accept/following-request/{follower_id?}', [FollowApiController::class, 'acceptFollowerRequest']);
        Route::post('/unaccepted/following-request/{follower_id?}', [FollowApiController::class, 'UnacceptedFollowerRequest']);
    });

    Route::group(['prefix' => 'game'], function () {
        Route::get('/start', [GameApiController::class, 'start']);
        Route::post('/next-question', [GameApiController::class, 'next']);
        Route::post('/finish', [GameApiController::class, 'finish']);
    });

    Route::group(['prefix' => 'guide'], function () {
        Route::post('/trips/store', [GuideTripApiController::class, 'store']);
        Route::post('/trips/update/{guide_trip_id?}', [GuideTripApiController::class, 'update']);
        Route::delete('/trips/delete/{guide_trip_id?}', [GuideTripApiController::class, 'delete']);
        Route::delete('/image/delete/{media_id}', [GuideTripApiController::class, 'DeleteImage']);
        Route::get('join/requests/list/{guide_trip_id?}',[GuideTripApiController::class, 'joinRequests']);
        Route::put('change/join/request/{status?}/{guide_trip_user_id?}',[GuideTripApiController::class, 'changeJoinRequestStatus']);
    });

    Route::group(['prefix' => 'user/guide-trip'], function () {
        Route::get('/subscription/{guide_trip_id}', [GuideTripUserApiController::class, 'allSubscription']);
        Route::post('/store', [GuideTripUserApiController::class, 'store']);
        Route::post('/update', [GuideTripUserApiController::class, 'update']);
        Route::delete('/delete/{guide_trip_id?}', [GuideTripUserApiController::class, 'delete']);

        // favorite of guide trip by user
        Route::post('favorite/{guide_trip_id?}', [GuideTripUserApiController::class, 'createFavoriteGuideTrip']);
        Route::delete('favorite/{guide_trip_id?}/delete', [GuideTripUserApiController::class, 'deleteFavoriteGuideTrip']);

        //Review of user on guide trip
        Route::post('add/review/{guide_trip_id?}', [GuideTripUserApiController::class, 'addReview']);
        Route::post('update/review/{guide_trip_id?}', [GuideTripUserApiController::class, 'updateReview']);
        Route::delete('delete/review/{guide_trip_id?}', [GuideTripUserApiController::class, 'deleteReview']);
        Route::post('review/{status?}/{review_id?}', [GuideTripUserApiController::class, 'likeDislike']);
    });

    Route::controller(GuideRatingController::class)->group(function () {
        Route::get('rating/guide/show/{guide_id?}', 'show');
        Route::post('rating/guide/store', 'create');
        Route::post('rating/guide/update', 'update');
        Route::delete('rating/guide/delete/{guide_id?}', 'delete');
    });
});

// All Routes For profile
Route::post('profile/update', [UserProfileController::class, 'update']);
Route::get('all/tags', [UserProfileController::class, 'allTags']);

Route::get('user/all/favorite', [UserProfileController::class, 'allFavorite']);
Route::post('user/set-location', [UserProfileController::class, 'setLocation']);
Route::post('delete/account', [AuthUserController::class, 'deleteAccount']);
Route::get('user/favorite/search', [UserProfileController::class, 'favSearch']);
Route::get('user/deactivate-account', [AuthUserController::class, 'deactivateAccount']);

Broadcast::routes();
