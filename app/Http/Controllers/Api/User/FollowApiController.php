<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Follow\CreateFollowRequest;
use App\Http\Requests\Api\User\Follow\DeleteFollowRequest;
use App\Rules\CheckIfFollowerFollowingExistsRule;
use App\Rules\CheckIfFollowerFollowingNotExistsRule;
use App\Rules\CheckIfFollowerFollowingUserRule;
use App\Rules\CheckIfFollowerFollowingUserWithAnyStatusRule;
use App\Rules\DiscoverJordanFollowRule;
use App\UseCases\Api\User\CategoryApiUseCase;
use App\UseCases\Api\User\FollowApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class FollowApiController extends Controller
{
    protected $followApiUseCase;

    public function __construct(FollowApiUseCase $followUseCase) {

        $this->followApiUseCase = $followUseCase;

    }
    /**
     * Display a listing of the resource.
     */
    public function follow(Request $request,$following_slug)
    {

        $validator = Validator::make(['following_slug' => $following_slug], [
            'following_slug' => ['required', 'exists:users,slug', new CheckIfFollowerFollowingExistsRule()],
        ],
            [
                'following_slug.required'=>__('validation.api.the-following-id-is-required'),
                'following_slug.exists'=>__('validation.api.the-following-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->follow($validator->validated()['following_slug']);
            return ApiResponse::sendResponse(200, __('app.api.the-following-request-sent-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function unfollow(Request $request,$following_slug)
    {

        $validator = Validator::make(['following_slug' => $following_slug], [
            'following_slug' => ['bail','required', 'exists:users,slug', new CheckIfFollowerFollowingNotExistsRule(),new DiscoverJordanFollowRule()],
            ],
            [
                'following_slug.required'=>__('validation.api.the-following-id-is-required'),
                'following_slug.exists'=>__('validation.api.the-following-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->unfollow($validator->validated()['following_slug']);
            return ApiResponse::sendResponse(200, __('app.api.follows-deleted-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function acceptFollowerRequest(Request $request,$follower_slug)
    {

        $validator = Validator::make(
            [
                'follower_slug' => $follower_slug,
            ],
            [
                'follower_slug' => ['bail','required', 'exists:users,slug', new CheckIfFollowerFollowingUserRule()],
            ],
            [
                'follower_slug.required' => __('validation.api.the-follower-id-is-required'),
                'follower_slug.exists' => __('validation.api.the-follower-id-does-not-exist'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->acceptFollower($validator->validated()['follower_slug']);
            return ApiResponse::sendResponse(200, __('app.api.accept-follow-request-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }

    }

    public function UnacceptedFollowerRequest(Request $request,$follower_slug)
    {

        $validator = Validator::make(
            [
                'follower_slug' => $follower_slug
            ],
            [
                'follower_slug' => ['required', 'exists:users,slug', new CheckIfFollowerFollowingUserWithAnyStatusRule()],
            ],
            [
                'follower_slug.required'=>__('validation.api.the-following-id-is-required'),
                'follower_slug.exists'=>__('validation.api.the-following-id-does-not-exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->unacceptedFollower($validator->validated()['follower_slug']);
            return ApiResponse::sendResponse(200, __('app.api.un-accept-follow-request-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }

    }

    public function followersRequest()
    {

        try{
            $follows = $this->followApiUseCase->followersRequest();
            return ApiResponse::sendResponse(200, __('app.api.followers-requests-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function Followers(Request $request,$user_slug)
    {

        $validator = Validator::make(['user_slug' => $user_slug], [
            'user_slug' => ['bail','required', 'exists:users,slug'],
        ],
            [
                'user_slug.required'=>__('validation.api.user-id-is-required'),
                'user_slug.exists'=>__('validation.api.user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->followers($validator->validated()['user_slug']);
            return ApiResponse::sendResponse(200, __('app.api.followers-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function Followings(Request $request,$user_slug)
    {

        $validator = Validator::make(['user_slug' => $user_slug], [
            'user_slug' => ['bail','required', 'exists:users,slug'],
        ],
            [
                'user_slug.required'=>__('validation.api.user-id-is-required'),
                'user_slug.exists'=>__('validation.api.user-id-does-not-exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->followings($validator->validate()['user_slug']);
            return ApiResponse::sendResponse(200, __('app.api.followings-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }


}
