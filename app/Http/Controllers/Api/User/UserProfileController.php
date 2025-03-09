<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Profile\SetLocationApiRequest;
use App\Http\Requests\Api\User\Profile\UpdateProfileApiRequest;
use App\Http\Requests\PlacesOfCurrentLocationRequest;
use App\UseCases\Api\User\UserProfileApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    protected $userProfileApiUseCase;

    public function __construct(UserProfileApiUseCase $userProfileApiUseCase)
    {

        $this->userProfileApiUseCase = $userProfileApiUseCase;
    }

    public function userDetails()
    {
        try {
            $userDetails = $this->userProfileApiUseCase->allUserDetails();
            return ApiResponse::sendResponse(200, __('app.api.user-details-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function update(UpdateProfileApiRequest $request)
    {
        try {
            $userUpdate = $this->userProfileApiUseCase->updateProfile($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-profile-updated-successfully'), $userUpdate);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function setLocation(SetLocationApiRequest $request)
    {
        try {
            $userLocation = $this->userProfileApiUseCase->setLocation($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-location-set-successfully'), $userLocation);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function allFavorite()
    {
        try {
            $userFav = $this->userProfileApiUseCase->allFavorite();
            return ApiResponse::sendResponse(200,  __('app.api.your-all-favorite-retrieved-successfully'), $userFav);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $users = $this->userProfileApiUseCase->search($query);

            return ApiResponse::sendResponse(200, __('app.api.the-users-retried-successfully'), $users);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function favSearch(Request $request)
    {
        $query = $request->input('query');
        try {
            $users = $this->userProfileApiUseCase->favSearch($query);

            return ApiResponse::sendResponse(200, __('app.api.the-searched-favorite-retrieved-successfully'), $users);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allTags()
    {
        try {
            $userDetails = $this->userProfileApiUseCase->allTags();
            return ApiResponse::sendResponse(200, __('app.api.all-tags-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function otherUserProfile(Request $request,$id)
    {

        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'exists:users,id'],
        ] ,[
            'id.required' => __('validation.api.user-id-is-required'),
            'id.exists' => __('validation.api.user-id-does-not-exists'),

        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $userDetails = $this->userProfileApiUseCase->otherUserDetails($id);
            return ApiResponse::sendResponse(200,__('app.api.user-details-retrieved-successfully'), $userDetails);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }

    }

    public function currentLocation(PlacesOfCurrentLocationRequest $request)
    {
        try {
            $userLocation = $this->userProfileApiUseCase->PlacesCurrentLocation($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.your-place-current-location-retrieved-successfully'), $userLocation);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

}
