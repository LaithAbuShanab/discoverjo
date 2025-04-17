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

    // USER PROFILE
    Route::get('/user/profile', [UserProfileController::class, 'userDetails'])->name('user.profile'); // DONE ✅
    Route::get('other/user/profile/{slug}', [UserProfileController::class, 'otherUserProfile'])->name('other.user.profile'); // DONE ✅

    // NOTIFICATION API
    Route::get('all/user/notifications', [UserProfileController::class, 'allNotifications'])->name('user.notifications'); // DONE ✅
    Route::put('make/notification/as-read/{id}', [UserProfileController::class, 'readNotification'])->name('user.read.notifications'); // DONE ✅
    Route::get('unread/user/notifications', [UserProfileController::class, 'unreadNotifications'])->name('user.unread.notifications'); // DONE ✅
    Route::delete('delete/notifications/{id}', [UserProfileController::class, 'deleteNotifications'])->name('user.delete.notifications'); // DONE ✅

    // FAVORITE SYSTEM
    Route::post('favorite/{type}/{slug}', [FavoriteApiController::class, 'favorite']); // DONE ✅
    Route::delete('favorite/{type}/{slug}/delete', [FavoriteApiController::class, 'unfavored']); // DONE ✅
    Route::get('user/all/favorite', [FavoriteApiController::class, 'allUserFavorites']); // DONE ✅
    Route::get('user/favorite/search', [FavoriteApiController::class, 'favSearch']); // DONE ✅

    // REVIEW SYSTEM
    Route::group(['prefix' => 'review'], function () {
        Route::get('all/{type}/{slug}', [ReviewApiController::class, 'reviews']); // DONE ✅
        Route::post('add/{type}/{slug}', [ReviewApiController::class, 'addReview']); // DONE ✅
        Route::put('update/{type}/{slug}', [ReviewApiController::class, 'updateReview']); // DONE ✅
        Route::delete('delete/{type}/{slug}', [ReviewApiController::class, 'deleteReview']); // DONE ✅
        Route::post('{status}/{review_id}', [ReviewApiController::class, 'likeDislike']); // DONE ✅
    });

    // VISITED PLACES
    Route::post('visited/place/{slug}', [PlaceApiController::class, 'createVisitedPlace']); // DONE ✅
    Route::delete('visited/place/{slug}/delete', [PlaceApiController::class, 'deleteVisitedPlace']); // DONE ✅

    // ALL ROUTES FOR INTEREST EVENT
    Route::group(['prefix' => 'event'], function () {
        Route::get('/interested/list', [EventApiController::class, 'interestList']); // DONE ✅
        Route::post('/interest/{slug}', [EventApiController::class, 'interest']); // DONE ✅
        Route::delete('/disinterest/{slug}', [EventApiController::class, 'disinterest']); // DONE ✅
    });

    // ALL ROUTES FOR INTEREST VOLUNTEERING
    Route::group(['prefix' => 'volunteering'], function () {
        Route::get('/interested/list', [VolunteeringApiController::class, 'interestedList']); // DONE ✅
        Route::post('/interest/{slug}', [VolunteeringApiController::class, 'interest']); // DONE ✅
        Route::delete('/disinterest/{slug}', [VolunteeringApiController::class, 'disinterest']); // DONE ✅
    });

    // ALL ROUTES FOR POST
    Route::prefix('post')->group(function () {
        Route::get('/followings', [PostApiController::class, 'followingPost']); // DONE ✅
        Route::post('/store', [PostApiController::class, 'store']); // DONE ✅
        Route::post('/update/{post_id}', [PostApiController::class, 'update']); // DONE ✅
        Route::get('show/{post_id}', [PostApiController::class, 'show']); // DONE ✅
        Route::delete('/image/delete/{media_id}', [PostApiController::class, 'DeleteImage']); // DONE ✅
        Route::delete('/delete/{post_id}', [PostApiController::class, 'destroy']); // DONE ✅
        Route::post('favorite/{post_id}', [PostApiController::class, 'createFavoritePost']); // DONE ✅
        Route::delete('favorite/{post_id}/delete', [PostApiController::class, 'deleteFavoritePost']); // DONE ✅
        Route::post('/like-dislike/{status}/{post_id}', [PostApiController::class, 'likeDislike']); // DONE ✅

        // COMMENTS SYSTEM
        Route::post('/comment/store', [CommentApiController::class, 'commentStore']); // DONE ✅
        Route::put('/comment/update/{comment_id}', [CommentApiController::class, 'commentUpdate']); // DONE ✅
        Route::delete('/comment/delete/{comment_id}', [CommentApiController::class, 'commentDelete']); // DONE ✅
        Route::post('comment/like-dislike/{status}/{comment_id}', [CommentApiController::class, 'likeDislike']); // DONE ✅
    });

    // ALL ROUTES FOR GUIDE
    Route::group(['prefix' => 'guide'], function () {
        Route::post('/trips/store', [GuideTripApiController::class, 'store']); // DONE ✅
        Route::get('/trips/{guide_slug}', [GuideTripApiController::class, 'tripsOfGuide']); // DONE ✅
        Route::post('/trips/update/{slug}', [GuideTripApiController::class, 'update']); // DONE ✅
        Route::delete('/trips/delete/{slug}', [GuideTripApiController::class, 'delete']); // DONE ✅
        Route::delete('/image/delete/{media_id}', [GuideTripApiController::class, 'DeleteImage']); // DONE ✅
        Route::get('join/requests/list/{slug}', [GuideTripApiController::class, 'joinRequests']); // DONE ✅
        Route::put('change/join/request/{status}/{guide_trip_user_id}', [GuideTripApiController::class, 'changeJoinRequestStatus']); // DONE ✅
    });

    // ALL ROUTES FOR GUIDE TRIP
    Route::group(['prefix' => 'user/guide-trip'], function () {
        Route::get('/subscription/{guide_trip_slug}', [GuideTripUserApiController::class, 'allSubscription']); // DONE ✅
        Route::post('/store/{guide_trip_slug}', [GuideTripUserApiController::class, 'store']); // DONE ✅
        Route::put('/update/{guide_trip_slug}', [GuideTripUserApiController::class, 'update']); // DONE ✅
        Route::delete('/delete/{guide_trip_slug}', [GuideTripUserApiController::class, 'delete']); // DONE ✅
    });

    // ALL ROUTES FOR GUIDE RATING
    Route::group(['prefix' => 'rating'], function () {
        Route::get('/guide/show/{guide_slug}', [GuideRatingController::class, 'show']); // DONE ✅
        Route::post('/guide/store/{guide_slug}', [GuideRatingController::class, 'create']); // DONE ✅
        Route::post('/guide/update/{guide_slug}', [GuideRatingController::class, 'update']); // DONE ✅
        Route::delete('/guide/delete/{guide_slug}', [GuideRatingController::class, 'delete']); // DONE ✅
    });

    // ALL ROUTES FOR FOLLOW
    Route::group(['prefix' => 'follow'], function () {
        Route::get('/followers/requests', [FollowApiController::class, 'followersRequest']); // DONE ✅
        Route::post('/create/{following_slug}', [FollowApiController::class, 'follow']); // DONE ✅
        Route::delete('/delete/{following_slug}', [FollowApiController::class, 'unfollow']); // DONE ✅
        Route::get('/followers/{user_slug}', [FollowApiController::class, 'followers']); // DONE ✅
        Route::get('/followings/{user_slug}', [FollowApiController::class, 'followings']); // DONE ✅
        Route::put('/accept/following-request/{follower_slug}', [FollowApiController::class, 'acceptFollowerRequest']); // DONE ✅
        Route::put('/unaccepted/following-request/{follower_slug}', [FollowApiController::class, 'UnacceptedFollowerRequest']); // DONE ✅
    });

    // ALL ROUTES FOR GROUP CHAT
    Route::group(['prefix' => 'chat'], function () {
        Route::get('/{conversation_id?}', [GroupChatController::class, 'messages']); // DONE ✅
        Route::get('members/{conversation_id?}', [GroupChatController::class, 'members']); // DONE ✅
        Route::post('/store', [GroupChatController::class, 'store']); // DONE ✅
    });

    // ALL ROUTES FOR TRIP
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
        Route::get('/invitations', [TripApiController::class, 'invitationTrips']); // DONE
        // Invitation Count
        Route::get('/invitation_count', [TripApiController::class, 'invitationCount']); // DONE ✅

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

    // ALL ROUTES FOR PLAN
    Route::group(['prefix' => 'plan'], function () {
        Route::get('/', [PlanApiController::class, 'index']); // DONE ✅
        Route::post('/create', [PlanApiController::class, 'create']); // DONE ✅
        Route::put('/update/{plan_slug}', [PlanApiController::class, 'update']); // DONE ✅
        Route::delete('/{plan_slug}/delete', [PlanApiController::class, 'destroy']); // DONE ✅
        Route::get('/show/{plan_slug}', [PlanApiController::class, 'show']); // DONE ✅
        Route::get('/my-plans', [PlanApiController::class, 'myPlans']); // DONE ✅
    });

    Route::group(['prefix' => 'game'], function () {
        Route::get('/start', [GameApiController::class, 'start']); // DONE ✅
        Route::post('/next-question', [GameApiController::class, 'next']); // DONE ✅
        Route::post('/finish', [GameApiController::class, 'finish']); // DONE ✅
    });
});

// ALL ROUTES FOR PROFILE
Route::post('profile/update', [UserProfileController::class, 'update']); // DONE ✅
Route::get('all/tags', [UserProfileController::class, 'allTags']); // DONE ✅
Route::post('user/set-location', [UserProfileController::class, 'setLocation']); // DONE ✅

// ALL ROUTES FOR DELETE ACCOUNT
Route::delete('delete/account', [AuthUserController::class, 'deleteAccount']); // DONE ✅
Route::put('user/deactivate-account', [AuthUserController::class, 'deactivateAccount']); // DONE ✅

Route::get('current/user/posts', [PostApiController::class, 'currentUserPosts']); // DONE ✅
Route::get('other/user/posts/{slug}', [PostApiController::class, 'otherUserPosts']); // DONE ✅


Broadcast::routes();
