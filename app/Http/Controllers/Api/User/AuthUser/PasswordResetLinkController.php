<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\PasswordRestLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordRestLinkRequest $request)
    {
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            dd($status);
            if ($status == Password::RESET_LINK_SENT) {
                return ApiResponse::sendResponse(200,  __('app.api.the-link-for-reset-password-sent-successfully'), null);
            } else {
                return ApiResponse::sendResponse(400, __('app.api.unable-to-send-the-link-for-reset-password'), null);
            }
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}
