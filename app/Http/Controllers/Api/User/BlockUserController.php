<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\BlockUserApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlockUserController extends Controller
{
    public function __construct(protected BlockUserApiUseCase $blockUserApiUseCase) {}

    public function listOfBlockedUsers()
    {
        try {
            $blockedUsers = $this->blockUserApiUseCase->listOfBlockedUsers();
            return ApiResponse::sendResponse(200, __('app.api.list-of-blocked-users-returned-successfully'), $blockedUsers);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function block(Request $request, $user_slug)
    {
        $authUser = Auth::guard('api')->user();

        $validator = Validator::make(
            ['user_slug' => $user_slug],
            [
                'user_slug' => [
                    'required',
                    'exists:users,slug',
                    function ($attribute, $value, $fail) use ($authUser) {
                        $targetUser = \App\Models\User::where('slug', $value)->first();

                        if (!$targetUser) {
                            return; // already handled by `exists`
                        }

                        if ($targetUser->id == 1) {
                            $fail(__('validation.api.cannot-block-discoverjo'));
                            return;
                        }

                        if ($targetUser->id === $authUser->id) {
                            $fail(__('validation.api.cannot-block-yourself'));
                            return;
                        }

                        if ($authUser->hasBlocked($targetUser)) {
                            $fail(__('validation.api.user-already-blocked'));
                        }
                    }
                ],
            ],
            [
                'user_slug.required' => __('validation.api.the-user-id-is-required'),
                'user_slug.exists' => __('validation.api.the-user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $follows = $this->blockUserApiUseCase->block($validator->validated()['user_slug']);
            return ApiResponse::sendResponse(200, __('app.api.the-user-blocked-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function unblock(Request $request, $user_slug)
    {
        $authUser = Auth::guard('api')->user();

        $validator = Validator::make(
            ['user_slug' => $user_slug],
            [
                'user_slug' => [
                    'required',
                    'exists:users,slug',
                    function ($attribute, $value, $fail) use ($authUser) {
                        $targetUser = \App\Models\User::where('slug', $value)->first();

                        if (!$targetUser) {
                            return; // Already handled by 'exists'
                        }

                        if ($targetUser->id === $authUser->id) {
                            $fail(__('validation.api.cannot-unblock-yourself'));
                            return;
                        }

                        if (!$authUser->hasBlocked($targetUser)) {
                            $fail(__('validation.api.user-not-blocked'));
                        }
                    }
                ],
            ],
            [
                'user_slug.required' => __('validation.api.the-user-id-is-required'),
                'user_slug.exists' => __('validation.api.the-user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors);
        }

        try {
            $follows = $this->blockUserApiUseCase->unblock($validator->validated()['user_slug']);
            return ApiResponse::sendResponse(200, __('app.api.the-user-unblocked-successfully'), $follows);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

}
