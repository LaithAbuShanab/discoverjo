<?php

use App\Http\Controllers\Api\User\AuthUser\AuthUserController;
use App\Http\Controllers\Api\User\CommentApiController;
use App\Http\Controllers\Api\User\EventApiController;
use App\Http\Controllers\Api\User\FavoriteApiController;
use App\Http\Controllers\Api\User\FollowApiController;
use App\Http\Controllers\Api\User\GameApiController;
use App\Http\Controllers\Api\User\GroupChatController;
use App\Http\Controllers\Api\User\GuideRatingController;
use App\Http\Controllers\Api\User\GuideTripApiController;
use App\Http\Controllers\Api\User\GuideTripUserApiController;
use App\Http\Controllers\Api\User\PlaceApiController;
use App\Http\Controllers\Api\User\PlanApiController;
use App\Http\Controllers\Api\User\PostApiController;
use App\Http\Controllers\Api\User\ReservationApiController;
use App\Http\Controllers\Api\User\ReviewApiController;
use App\Http\Controllers\Api\User\TripApiController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\VolunteeringApiController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\PropertyReservationApiController;
use App\Http\Controllers\Api\User\SingleChatController;

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
        Route::get('/trips/detail/update/{slug}', [GuideTripApiController::class, 'detailUpdate']); // DONE ✅
        Route::delete('/trips/delete/{slug}', [GuideTripApiController::class, 'delete']); // DONE ✅
        Route::delete('/image/delete/{media_id}', [GuideTripApiController::class, 'DeleteImage']); // DONE ✅
        Route::get('join/requests/list/{slug}', [GuideTripApiController::class, 'joinRequests']); // DONE ✅
        Route::put('change/join/request/{status}/{guide_trip_user_id}', [GuideTripApiController::class, 'changeJoinRequestStatus']); // DONE ✅
    });

    // ALL ROUTES FOR GUIDE TRIP
    Route::group(['prefix' => 'user/guide-trip'], function () {
        //show all subscriptions
        Route::get('/subscription/{guide_trip_slug}', [GuideTripUserApiController::class, 'allSubscription']); // DONE ✅
        //add new list of subscription
        Route::post('/store/{guide_trip_slug}', [GuideTripUserApiController::class, 'store']); // DONE ✅
        //delete all subscriptions
        Route::delete('/delete/{guide_trip_slug}', [GuideTripUserApiController::class, 'delete']); // DONE ✅
        //update all subscriptions should review it
        //        Route::put('/update/{guide_trip_slug}', [GuideTripUserApiController::class, 'update']); // DONE ✅
        //get single subscription
        Route::get('/single/subscription/{subscription_id}', [GuideTripUserApiController::class, 'singleSubscription']);

        //update single subscription
        Route::put('/update/single/subscription/{subscription_id}', [GuideTripUserApiController::class, 'updateSingleSubscription']);

        //create single subscription
        Route::post('/store/single/subscription/{guide_trip_slug}', [GuideTripUserApiController::class, 'storeSingleSubscription']);
        //delete single subscription
        Route::delete('/single/subscription/delete/{subscription_id}', [GuideTripUserApiController::class, 'deleteSingleSubscription']);
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
        Route::delete('/remove/follower/{follower_slug}', [FollowApiController::class, 'removeFollower']); // DONE ✅
        Route::get('/followers/{user_slug}', [FollowApiController::class, 'followers']); // DONE ✅
        Route::get('/followings/{user_slug}', [FollowApiController::class, 'followings']); // DONE ✅
        Route::put('/accept/following-request/{follower_slug}', [FollowApiController::class, 'acceptFollowerRequest']); // DONE ✅
        Route::put('/unaccepted/following-request/{follower_slug}', [FollowApiController::class, 'UnacceptedFollowerRequest']); // DONE ✅
    });

    // ✅ ALL ROUTES FOR CHAT (Group + Single)
    Route::group(['prefix' => 'chat'], function () {

        /*
        |--------------------------------------------------------------------------
        | GROUP CHAT ROUTES
        |--------------------------------------------------------------------------
        */
        Route::get('/{conversation_id?}', [GroupChatController::class, 'messages']); // ✅ Get group chat messages
        Route::get('members/{conversation_id?}', [GroupChatController::class, 'members']); // ✅ Get group chat members
        Route::post('/store', [GroupChatController::class, 'store']); // ✅ Send group chat message

        /*
        |--------------------------------------------------------------------------
        | SINGLE CHAT ROUTES
        |--------------------------------------------------------------------------
        */
        Route::post('/conversations/{user_slug}', [SingleChatController::class, 'createSingleChat']); // ✅ Create or get single chat
        Route::get('/list/conversations', [SingleChatController::class, 'listConversations']); // ✅ List user conversations
        Route::get('/conversation/{conversation_id}/messages', [SingleChatController::class, 'singleConversation']); // ✅ Get single conversation messages
        Route::post('/messages', [SingleChatController::class, 'store']); // ✅ Send message (single chat)
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

    // ALL ROUTES FOR RESERVATION
    Route::group(['prefix' => 'service'], function () {
        // API OF USER
        Route::get('/reservation/{service_slug}/date/{date}', [ReservationApiController::class, 'reservationDate']); // DONE ✅
        Route::post('/make/reservation', [ReservationApiController::class, 'serviceReservation']); // DONE ✅
        Route::put('/reservation/{id}/update', [ReservationApiController::class, 'updateReservation']); // DONE ✅
        Route::Delete('/reservation/{id}/delete', [ReservationApiController::class, 'deleteReservation']); // DONE ✅
        Route::get('/reservations/{service_slug}', [ReservationApiController::class, 'UserServiceReservations']); // DONE ✅
        Route::get('all/reservations', [ReservationApiController::class, 'allReservations']); // DONE ✅

        // API OF PROVIDER
        //1- change status
        Route::put('/reservation/{id}/status/{status}', [ReservationApiController::class, 'changeStatusReservation']); // DONE ✅
        //2- list of pending request pagination
        Route::get('provider/request/reservations/list/{service_slug}', [ReservationApiController::class, 'providerRequestReservations']); // DONE ✅
        //3- list of reservation accepted pagination
        Route::get('provider/approved/reservations/list/{service_slug}', [ReservationApiController::class, 'approvedRequestReservations']); // DONE ✅

    });

    // ALL ROUTES FOR PROPERTY
    Route::prefix('property')->group(function () {
        // First widget for reservation
        Route::get('check/available/{property_slug}/{period_type}', [PropertyReservationApiController::class, 'checkAvailable']); // DONE ✅
        // Check availability for custom month and year
        Route::get('available/{property_slug}/{period_type}/{month}/{year}', [PropertyReservationApiController::class, 'checkAvailableMonth']); // DONE ✅
        // Check reservation price
        Route::get('check/price', [PropertyReservationApiController::class, 'checkPrice']); // DONE ✅
        // Make reservation of property
        Route::post('make/reservation', [PropertyReservationApiController::class, 'makeReservation']); // DONE ✅
        // Update reservation of property
        Route::put('reservation/{id}/update', [PropertyReservationApiController::class, 'updateReservation']); // DONE ✅
        // Delete property reservation
        Route::delete('reservation/{id}/delete', [PropertyReservationApiController::class, 'deleteReservation']); // DONE ✅
        // All reservations for specific property
        Route::get('reservations/{property_slug}', [PropertyReservationApiController::class, 'allPropertyReservations']); // DONE ✅
        // All reservations
        Route::get('all/reservations', [PropertyReservationApiController::class, 'allReservations']); // DONE ✅
        // API of Host
        Route::put('reservation/{id}/status/{status}', [PropertyReservationApiController::class, 'changeStatusReservation']); // DONE ✅
        Route::get('host/request/reservations/list/{property_slug}', [PropertyReservationApiController::class, 'RequestReservations']); // DONE ✅
        Route::get('host/approved/reservations/list/{property_slug}', [PropertyReservationApiController::class, 'approvedRequestReservations']); // DONE ✅
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

Route::post('/report/user', [UserProfileController::class, 'report']);


Broadcast::routes();
