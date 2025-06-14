<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Profile\SetLocationApiRequest;
use App\Http\Requests\Api\User\Profile\UpdateProfileApiRequest;
use App\Http\Requests\Api\User\Warning\WarningRequest;
use App\Http\Requests\PlacesOfCurrentLocationRequest;
use App\Models\Warning;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use App\Rules\CheckIfNotificationBelongToUserRule;
use App\Rules\CheckIfUserActiveRule;
use App\UseCases\Api\User\UserProfileApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function __construct(protected UserProfileApiUseCase $userProfileApiUseCase)
    {

        $this->userProfileApiUseCase = $userProfileApiUseCase;
    }

    public function userDetails()
    {
        try {
            $userDetails = $this->userProfileApiUseCase->allUserDetails();
            return ApiResponse::sendResponse(200, __('app.api.user-details-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function update(UpdateProfileApiRequest $request)
    {
        try {
            $userUpdate = $this->userProfileApiUseCase->updateProfile($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-profile-updated-successfully'), $userUpdate);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function setLocation(SetLocationApiRequest $request)
    {
        try {
            $userLocation = $this->userProfileApiUseCase->setLocation($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-location-set-successfully'), $userLocation);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allFavorite()
    {
        try {
            $userFav = $this->userProfileApiUseCase->allFavorite();
            return ApiResponse::sendResponse(200,  __('app.api.your-all-favorite-retrieved-successfully'), $userFav);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => ['bail','nullable','string','max:255','regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u',new CheckIfHasInjectionBasedTimeRule()]
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['query']);
        }
        $validatedQuery = $validator->validated()['query'];
        try {
            $users = $this->userProfileApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-users-retried-successfully'), $users);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function favSearch(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => 'bail|nullable|string|max:255'
        ]);
        $validatedQuery = $validator->validated()['query'];
        try {
            $users = $this->userProfileApiUseCase->favSearch($validatedQuery);

            return ApiResponse::sendResponse(200, __('app.api.the-searched-favorite-retrieved-successfully'), $users);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allTags()
    {
        try {
            $userDetails = $this->userProfileApiUseCase->allTags();
            return ApiResponse::sendResponse(200, __('app.api.all-tags-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function otherUserProfile(Request $request, $slug)
    {

        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:users,slug', new CheckIfUserActiveRule()],
        ], [
            'slug.required' => __('validation.api.user-id-is-required'),
            'slug.exists' => __('validation.api.user-id-does-not-exists'),

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $data = $validator->validated();
            $userDetails = $this->userProfileApiUseCase->otherUserDetails($data['slug']);
            return ApiResponse::sendResponse(200, __('app.api.user-details-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function currentLocation(PlacesOfCurrentLocationRequest $request)
    {
        try {
            $userLocation = $this->userProfileApiUseCase->PlacesCurrentLocation($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-place-current-location-retrieved-successfully'), $userLocation);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allNotifications()
    {
        try {
            $notifications = $this->userProfileApiUseCase->allNotifications();
            return ApiResponse::sendResponse(200,  __('app.api.your-notification-retrieved-successfully'), $notifications);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function readNotification($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'exists:notifications,id', new CheckIfNotificationBelongToUserRule()],
        ], [
            'id.required' => __('validation.api.user-id-is-required'),
            'id.exists' => __('validation.api.user-id-does-not-exists'),

        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $notifications = $this->userProfileApiUseCase->readNotification($validator->validated()['id']);
            return ApiResponse::sendResponse(200,  __('app.api.notification-read-successfully'), $notifications);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function unreadNotifications()
    {
        try {
            $notifications = $this->userProfileApiUseCase->unreadNotifications();
            return ApiResponse::sendResponse(200,  __('app.api.your-unread-notification-retrieved-successfully'), $notifications);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function deleteNotifications($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['bail', 'required', 'exists:notifications,id', new CheckIfNotificationBelongToUserRule()],
        ], [
            'id.required' => __('validation.api.notification-id-is-required'),
            'id.exists' => __('validation.api.notification-id-does-not-exists'),

        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $notifications = $this->userProfileApiUseCase->deleteNotifications($validator->validated()['id']);
            return ApiResponse::sendResponse(200,  __('app.api.notification-deleted-successfully'), $notifications);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function report(WarningRequest $request)
    {
        try {
            $report = $this->userProfileApiUseCase->warning($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.report-created-successfully'), $report);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

}
