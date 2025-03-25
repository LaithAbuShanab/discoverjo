<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\NewPasswordRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\PasswordReset;

class NewPasswordController extends Controller
{
    public function __invoke(NewPasswordRequest $request)
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                return response()->json(['message' => __('app.api.your-password-reset-successfully')], 200);
            } else {
                return response()->json(['message' => __('app.api.unable-to-reset-your-password')], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' =>  __('app.api.an-error-occurred-while-resetting-the-password')], 500);
        }
    }
}
