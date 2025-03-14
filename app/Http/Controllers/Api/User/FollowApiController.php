<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Follow\CreateFollowRequest;
use App\Http\Requests\Api\User\Follow\DeleteFollowRequest;
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
    public function follow(CreateFollowRequest $request)
    {
        try{
            $follows = $this->followApiUseCase->follow($request);
            return ApiResponse::sendResponse(200, __('app.api.the-following-request-sent-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function unfollow(Request $request)
    {
        $id = $request->following_id;

        $validator = Validator::make(['following_id' => $id], [
            'following_id' => ['required', 'exists:users,id', new CheckIfFollowerFollowingNotExistsRule(),new DiscoverJordanFollowRule()],
            ],
            [
                'following_id.required'=>__('validation.api.the-following-id-is-required'),
                'following_id.exists'=>__('validation.api.the-following-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->unfollow($request->following_id);
            return ApiResponse::sendResponse(200, __('app.api.follows-deleted-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function acceptFollowerRequest(Request $request)
    {
        $id = $request->follower_id;

        $validator = Validator::make(
            [
                'follower_id' => $id,
            ],
            [
                'follower_id' => ['required', 'exists:users,id', new CheckIfFollowerFollowingUserRule()],
            ],
            [
                'follower_id.required' => __('validation.api.the-follower-id-is-required'),
                'follower_id.exists' => __('validation.api.the-follower-id-does-not-exist'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->acceptFollower($request->follower_id);
            return ApiResponse::sendResponse(200, __('app.api.accept-follow-request-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }

    }

    public function UnacceptedFollowerRequest(Request $request)
    {
        $id = $request->follower_id;

        $validator = Validator::make(
            [
                'follower_id' => $id
            ],
            [
                'follower_id' => ['required', 'exists:users,id', new CheckIfFollowerFollowingUserWithAnyStatusRule()],
            ],
            [
                'following_id.required'=>__('validation.api.the-following-id-is-required'),
                'following_id.exists'=>__('validation.api.the-following-id-does-not-exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->unacceptedFollower($request->follower_id);
            return ApiResponse::sendResponse(200, __('app.api.un-accept-follow-request-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }

    }

    public function followersRequest()
    {
        $id = Auth::guard('api')->user()->id;

        try{
            $follows = $this->followApiUseCase->followersRequest($id);
            return ApiResponse::sendResponse(200, __('app.api.followers-requests-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function Followers(Request $request)
    {

        $validator = Validator::make(['user_id' => $request->user_id], [
            'user_id' => ['required', 'exists:users,id'],
        ],
            [
                'user_id.required'=>__('validation.api.user-id-is-required'),
                'user_id.exists'=>__('validation.api.user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->followers($request->user_id);
            return ApiResponse::sendResponse(200, __('app.api.followers-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function Followings(Request $request)
    {

        $validator = Validator::make(['user_id' => $request->user_id], [
            'user_id' => ['required', 'exists:users,id'],
        ],
            [
                'user_id.required'=>__('validation.api.user-id-is-required'),
                'user_id.exists'=>__('validation.api.user-id-does-not-exists'),
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $follows = $this->followApiUseCase->followings($request->user_id);
            return ApiResponse::sendResponse(200, __('app.api.followings-retrieved-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }


}
