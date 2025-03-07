<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\CurrentUserNewPasswordRequest;
use App\Http\Requests\Api\User\Auth\NewPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\PasswordReset;

class CurrentUserNewPasswordController extends Controller
{
    public function __invoke(CurrentUserNewPasswordRequest $request)
    {
        try {
            $user = Auth::guard('api')->user(); // Get the currently authenticated user

            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            return response()->json(['message' => __('app.auth.api.your-password-reset-successfully')], 200);

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => __('app.auth.api.an-error-occurred-while-resetting-the-password')], 500);
        }
    }
}
