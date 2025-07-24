<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\UseCases\Api\User\BlockUserApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlockUserController extends Controller
{
    public function __construct(protected BlockUserApiUseCase $blockUserApiUseCase) {}

    public function toggleBlock(Request $request, $user_slug)
    {
        $authUser = User::find(Auth::user()->id);

        $validator = Validator::make(
            ['user_slug' => $user_slug],
            [
                'user_slug' => [
                    'required',
                    'exists:users,slug',
                    function ($attribute, $value, $fail) use ($authUser) {
                        $targetUser = \App\Models\User::where('slug', $value)->first();

                        if (!$targetUser) {
                            return; // exists rule already handles this
                        }

                        if ($targetUser->id == 1) {
                            $fail(__('validation.api.cannot-block-discoverjo'));
                            return;
                        }

                        if ($authUser->id === $targetUser->id) {
                            $fail(__('validation.api.cannot-block-yourself'));
                            return;
                        }
                    }
                ]
            ],
            [
                'user_slug.required' => __('validation.api.the-user-id-is-required'),
                'user_slug.exists' => __('validation.api.the-user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->all());
        }

        $targetUser = \App\Models\User::where('slug', $user_slug)->first();

        try {
            $alreadyBlocked = $authUser->hasBlocked($targetUser);

            if ($alreadyBlocked) {
                $this->blockUserApiUseCase->unblock($user_slug);
                $message = __('app.api.the-user-unblocked-successfully');
            } else {
                $this->blockUserApiUseCase->block($user_slug);
                $message = __('app.api.the-user-blocked-successfully');
            }

            return ApiResponse::sendResponse(200, $message, ['blocked' => !$alreadyBlocked]);
        } catch (\Exception $e) {
            Log::error('Block/Unblock Toggle Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}
