<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\LoginApiUserRequest;
use App\Http\Requests\Api\User\Auth\RegisterApiUserRequest;
use App\Http\Resources\UserLoginResource;
use App\Models\User;
use App\UseCases\Api\User\AuthApiUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthUserController extends Controller
{
    protected $authApiUseCase;

    public function __construct(AuthApiUseCase $authApiUseCase)
    {
        $this->authApiUseCase = $authApiUseCase;
    }

    public function login(LoginApiUserRequest $request)
    {
        try {
            $user = $this->authApiUseCase->login($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.you-logged-in-successfully'), $user);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(401, $e->getMessage());
        }
    }

    public function register(RegisterApiUserRequest $request)
    {
        $lang = $request->header('Content-Language') ? $request->header('Content-Language') : 'ar';
        try {
            $user = $this->authApiUseCase->register($request->validated(), $lang);
            return ApiResponse::sendResponse(200, __('app.api.you-register-successfully'), $user);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            $this->authApiUseCase->logout();
            return ApiResponse::sendResponse(200, __('app.api.you-logged-out-successfully'), null);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function resetPassword($lang)
    {
        return view('users.auth.rest_password', compact('lang'));
    }

    public function deleteAccount(Request $request)
    {
        try {
            $this->authApiUseCase->deleteAccount();
            return ApiResponse::sendResponse(201, __('app.api.your-account-deleted-successfully'), null);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function deactivateAccount()
    {
        try {
            $this->authApiUseCase->deactivateAccount();
            return ApiResponse::sendResponse(200, __('app.api.your-account-deactivated-successfully'), null);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function facebookPage()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookRedirect()
    {
        try {
            $user = Socialite::driver('facebook')->stateless()->user();

            // Attempt to find the user by email or Facebook ID
            $userModel = User::where('email', $user->email)->first();

            if ($userModel) {

                $userModel->update([
                    'facebook_id' => $user->id,
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1],
                ]);
            } else {
                // Create a new user
                $userModel = User::create([
                    'facebook_id' => $user->id,
                    'email' => $user->email,
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1],
                ]);
                adminNotification($userModel);
            }

            // Handle avatar if it exists
            if ($user->avatar) {
                $userModel->addMediaFromUrl($user->avatar)->toMediaCollection('avatar');
            }

            // Log the user in
            Auth::login($userModel);

            // Generate access token
            $token = $userModel->createToken('asma')->accessToken;
            $userModel->email_verified_at = now();

            $userModel->save();
            $userModel->token = $token;

            // Return success response
            return ApiResponse::sendResponse(200, 'User logged in successfully', new UserLoginResource($userModel));
        } catch (\Exception $e) {
            // Handle exceptions
            return ApiResponse::sendResponseError(500, $e->getMessage());
        }
    }

    public function googlePage()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleRedirect()
    {

        try {
            $user = Socialite::driver('google')->stateless()->user();
            if (User::where('email', $user->email)->where('google_id', $user->id)->exists()) {
                $userModel = User::where('email', $user->email)->orWhere('google_id', $user->id)->first();
                $userModel->update([
                    'first_name' => $user->given_name,
                    'last_name' => $user->family_name,
                ]);
            } elseif (User::where('email', $user->email)->exists()) {
                $userModel = User::where('email', $user->email)->first();
                $userModel->update([
                    'google_id' => $user->id,
                    'first_name' => $user->given_name,
                    'last_name' => $user->family_name,
                ]);
            } else {
                $userModel = User::create([
                    'google_id' => $user->id,
                    'email' => $user->email,
                    'username' => str_replace(' ', '-', strtolower($user->name)),
                    'first_name' => $user->given_name,
                    'last_name' => $user->family_name,
                ]);
                adminNotification($userModel);
            }

            if ($user->avatar !== null) {
                $userModel->addMediaFromUrl($user->avatar)->toMediaCollection('avatar');
            }

            $token = $userModel->createToken('mobile')->accessToken;
            $userModel->markEmailAsVerified();
            $userModel->token = $token;

            return ApiResponse::sendResponse(200,    __('app.api.you-logged-in-successfully'), new UserLoginResource($userModel));
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(500, $e->getMessage());
        }
    }

    public function twitterPage()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function twitterRedirect()
    {
        try {
            $user = Socialite::driver('twitter')->user();
            if (User::where('email', $user->email)->where('twitter_id', $user->id)->exists()) {
                $userModel = User::where('email', $user->email)->where('twitter_id', $user->id)->first();
                $userModel->update([
                    'username' => str_replace(' ', '-', strtolower($user->name)),
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1],
                ]);
                if ($user->avatar !== null) {
                    $userModel->addMediaFromUrl($user->avatar)->toMediaCollection('avatar');
                }
            } elseif (User::where('email', $user->email)->exists()) {
                throw new \Exception('This email is already associated with another account. Please login instead.');
            } else {
                $userModel = User::create([
                    'twitter_id' => $user->id,
                    'email' => $user->email,
                    'username' => str_replace(' ', '-', strtolower($user->name)),
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1],
                ]);
                adminNotification($userModel);
            }

            $token = $userModel->createToken('mobile')->accessToken;
            $userModel->markEmailAsVerified();
            $userModel->verified_email = true;
            $userModel->token = $token;

            return ApiResponse::sendResponse(200,   __('app.api.you-logged-in-successfully'), new UserLoginResource($userModel));
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(500, $e->getMessage());
        }
    }


    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }


    public function handleProviderCallback($provider): JsonResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $authUser = $this->findOrCreateUser($socialUser, $provider);
            $authUser->email_verified_at = now();
            $authUser->save();

            // Log the user in
            Auth::login($authUser);

            // Generate access token
            $token = $authUser->createToken('mobile')->accessToken;
            $authUser->token = $token;

            return ApiResponse::sendResponse(200, 'User logged in successfully', new UserLoginResource($authUser));
        } catch (\Exception $e) {
            // Handle exceptions
            return ApiResponse::sendResponseError(500, $e->getMessage());
        }
    }

    /**
     * Splits a name into first and last name.
     *
     * @param  string  $name  The name to be split.
     * @return array An array containing the first name and last name.
     */
    public function split_name($name): array
    {
        $name = trim($name);

        $last_name = strpos($name, ' ') === false ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));

        return [$first_name, $last_name];
    }

    /**
     * Finds or creates a user based on the social user and provider.
     *
     * @param object $socialUser  The social user object.
     * @param string $provider  The provider name.
     * @return object The created or existing user object.
     */
    private function findOrCreateUser(object $socialUser, string $provider): object
    {
        if ($authUser = User::where($provider . '_id', $socialUser->id)->first()) {
            return User::findOrFail($authUser->id);
        }
        if ($authUser = User::where('email', $socialUser->email)->first()) {
            $authUser->update([
                $provider . '_id' => $socialUser->id,
            ]);

            return $authUser;
        }
        $name = $socialUser->getName();

        $name_parts = $this->split_name($name);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1];
        $email = $socialUser->getEmail();
        $username = strstr($email, '@', true);

        if ($email === '') {
            Log::error('Social Login does not have email!');

            return $this->errorResponse(null, 'Email address is required!', 400);
        }

        $user = User::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'name' => $name,
            'email' => $email,
            'username' => $username,
            $provider . '_id' => $socialUser->id,

        ]);


        return $user;
    }
}
