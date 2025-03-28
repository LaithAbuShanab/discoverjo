<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\User\AuthUser\AuthUserController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\PlaceApiController;
use App\Http\Controllers\Api\User\TripApiController;
use App\Http\Controllers\Api\User\EventApiController;
use App\Http\Controllers\Api\User\VolunteeringApiController;
use App\Http\Controllers\Api\User\PlanApiController;
use App\Http\Controllers\Api\User\PostApiController;
use App\Http\Controllers\Api\User\FollowApiController;
use App\Http\Controllers\Api\User\CommentApiController;
use App\Http\Controllers\Api\User\GameApiController;
use App\Http\Controllers\Api\User\GuideTripApiController;
use App\Http\Controllers\Api\User\GuideTripUserApiController;
use App\Http\Controllers\Api\User\GuideRatingController;
use App\Http\Controllers\Api\User\GroupChatController;
use App\Http\Controllers\Api\User\FavoriteApiController;
use App\Http\Controllers\Api\User\ReviewApiController;

Route::middleware(['firstLogin'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'userDetails'])->name('user.profile');
    Route::get('other/user/profile/{slug}', [UserProfileController::class, 'otherUserProfile'])->name('other.user.profile');
    Route::get('all/user/notifications', [UserProfileController::class, 'allNotifications'])->name('user.notifications');
    Route::put('make/notification/as-read/{id}', [UserProfileController::class, 'readNotification'])->name('user.notifications');

    //favorite system
    Route::post('favorite/{type}/{slug}', [FavoriteApiController::class, 'favorite']);
    Route::delete('favorite/{type}/{slug}/delete', [FavoriteApiController::class, 'unfavored']);
    Route::get('user/all/favorite', [FavoriteApiController::class, 'allUserFavorites']);
    Route::get('user/favorite/search', [FavoriteApiController::class, 'favSearch']);

    //review system
    Route::group(['prefix' => 'review'], function () {
        Route::get('all/{type}/{slug}', [ReviewApiController::class, 'reviews']);
        Route::post('add/{type}/{slug}', [ReviewApiController::class, 'addReview']); // NOTIFICATION(1)
        Route::put('update/{type}/{slug}', [ReviewApiController::class, 'updateReview']);
        Route::delete('delete/{type}/{slug}', [ReviewApiController::class, 'deleteReview']);
        Route::post('{status}/{review_id}', [ReviewApiController::class, 'likeDislike']); // NOTIFICATION(2)
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

    Route::prefix('post')->group(function () {
        Route::get('/followings', [PostApiController::class, 'followingPost']);
        Route::post('/store', [PostApiController::class, 'store']); // NOTIFICATION(3)
        Route::post('/update/{post_id}', [PostApiController::class, 'update']);
        Route::get('show/{post_id}', [PostApiController::class, 'show']);
        Route::delete('/image/delete/{media_id}', [PostApiController::class, 'DeleteImage']);
        Route::delete('/delete/{post_id}', [PostApiController::class, 'destroy']);
        Route::post('favorite/{post_id}', [PostApiController::class, 'createFavoritePost']);
        Route::delete('favorite/{post_id}/delete', [PostApiController::class, 'deleteFavoritePost']);
        Route::post('/like-dislike/{status}/{post_id}', [PostApiController::class, 'likeDislike']);  // NOTIFICATION(4)

        //comment system
        Route::post('/comment/store', [CommentApiController::class, 'commentStore']); // NOTIFICATION(5)
        Route::put('/comment/update/{comment_id}', [CommentApiController::class, 'commentUpdate']);
        Route::delete('/comment/delete/{comment_id}', [CommentApiController::class, 'commentDelete']);
        Route::post('comment/like-dislike/{status}/{comment_id}', [CommentApiController::class, 'likeDislike']);  // NOTIFICATION(6)
    });

    Route::group(['prefix' => 'guide'], function () {
        Route::post('/trips/store', [GuideTripApiController::class, 'store']);
        Route::post('/trips/update/{slug}', [GuideTripApiController::class, 'update']);
        Route::delete('/trips/delete/{slug}', [GuideTripApiController::class, 'delete']);
        Route::delete('/image/delete/{media_id}', [GuideTripApiController::class, 'DeleteImage']);
        Route::get('join/requests/list/{slug}', [GuideTripApiController::class, 'joinRequests']); // NOTIFICATION(16)
        Route::put('change/join/request/{status}/{guide_trip_user_id}', [GuideTripApiController::class, 'changeJoinRequestStatus']); // NOTIFICATION(17)
    });

    Route::group(['prefix' => 'user/guide-trip'], function () {
        Route::get('/subscription/{guide_trip_slug}', [GuideTripUserApiController::class, 'allSubscription']);
        Route::post('/store/{guide_trip_slug}', [GuideTripUserApiController::class, 'store']); // NOTIFICATION(15)
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
        Route::post('/create/{following_slug}', [FollowApiController::class, 'follow']); // NOTIFICATION(7)
        Route::delete('/delete/{following_slug}', [FollowApiController::class, 'unfollow']);
        Route::get('/followers/{user_slug}', [FollowApiController::class, 'followers']);
        Route::get('/followings/{user_slug}', [FollowApiController::class, 'followings']);
        Route::put('/accept/following-request/{follower_slug}', [FollowApiController::class, 'acceptFollowerRequest']); // NOTIFICATION(8)
        Route::put('/unaccepted/following-request/{follower_slug}', [FollowApiController::class, 'UnacceptedFollowerRequest']);
    });

    Route::group(['prefix' => 'chat'], function () {
        Route::get('/{conversation_id?}', [GroupChatController::class, 'messages']);
        Route::get('members/{conversation_id?}', [GroupChatController::class, 'members']);
        Route::post('/store', [GroupChatController::class, 'store']); // NOTIFICATION(9)
    });

    // All Routes For Trip
    Route::group(['prefix' => 'trip'], function () {
        // Get Tags
        Route::get('/tags', [TripApiController::class, 'tags']); // DONE ✅
        // Get Trips
        Route::get('/', [TripApiController::class, 'index']); // DONE ✅
        // Create Trips
        Route::post('/create', [TripApiController::class, 'create']); // DONE ✅ // NOTIFICATION(10)
        // Private Trips
        Route::get('/private', [TripApiController::class, 'privateTrips']); // DONE ✅
        // User Trips Requests
        Route::post('/user/{status}', [TripApiController::class, 'acceptCancel']); // DONE ✅ // NOTIFICATION(14)
        // Invitations Trips
        Route::get('/invitations', [TripApiController::class, 'invitationTrips']); // DONE ✅

        // Middleware Grouped Routes
        Route::middleware('CheckTripStatus')->group(function () {
            // Joining Trips
            Route::post('/join/{trip_slug}', [TripApiController::class, 'join']); // DONE ✅ // NOTIFICATION(12)
            // Canceling Join
            Route::delete('/join/cancel/{trip_slug}', [TripApiController::class, 'cancelJoin']); // DONE ✅
            // Trip Details
            Route::get('/details/{trip_slug}', [TripApiController::class, 'tripDetails']); // DONE ✅
            // Delete Trips
            Route::delete('/delete/{trip_slug}', [TripApiController::class, 'remove']); // DONE ✅
            // Update Trips
            Route::put('/update/{trip_slug}', [TripApiController::class, 'update']); // DONE ✅
            // Invitations Trips Accept or Cancel
            Route::post('/invitation-status/{status}', [TripApiController::class, 'acceptCancelInvitation']); // DONE ✅ // NOTIFICATION(13)
            // Remove User From Trip After User Joined
            Route::post('/remove-user/{trip_slug}', [TripApiController::class, 'removeUser']); // DONE ✅
        });
    });

    // All Routes For Plan
    Route::group(['prefix' => 'plan'], function () {
        Route::get('/', [PlanApiController::class, 'index']); // DONE ✅
        Route::post('/create', [PlanApiController::class, 'create']); // DONE ✅
        Route::put('/update/{plan_slug}', [PlanApiController::class, 'update']); // DONE ✅
        Route::delete('/{plan_slug}/delete', [PlanApiController::class, 'destroy']); // DONE ✅
        Route::get('/show/{plan_slug}', [PlanApiController::class, 'show']); // DONE ✅
        Route::get('/my-plans', [PlanApiController::class, 'myPlans']); // DONE ✅
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
Route::delete('delete/account', [AuthUserController::class, 'deleteAccount']);
Route::put('user/deactivate-account', [AuthUserController::class, 'deactivateAccount']);


Route::get('current/user/posts', [PostApiController::class, 'currentUserPosts']);
Route::get('other/user/posts/{slug}', [PostApiController::class, 'otherUserPosts']);


Broadcast::routes();
