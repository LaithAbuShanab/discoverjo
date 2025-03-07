<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use App\Models\Admin;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Plan;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use App\Notifications\Admin\NewUserRegisteredNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use mysql_xdevapi\Exception;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class EloquentAuthApiRepository implements AuthApiRepositoryInterface
{
    public function register($userData)
    {
        $token = $userData['device_token'];
        unset($userData['device_token']);

        //create the user
        $user = User::create($userData);

        //save device token for the user
        $device_token = new DeviceToken();
        $device_token->user_id = $user->id;
        $device_token->token = $token;
        $device_token->save();

        //make the user following discover jo when registered
        Follow::create([
            'following_id' => 1,
            'follower_id' => $user->id,
            'status' => 1
        ]);

        //this event for real time notification
        event(new Registered($user));

        // to save the notification in database
        Notification::send(Admin::all(), new NewUserRegisteredNotification($user));

        // it is for notification by socket we will change it to filament so we dont need it
        // Http::post('http://127.0.0.1:3000/notifications');
        return (new UserResource($user));
    }

    public function login($userData)
    {
        $credentials = [];

        if (isset($userData['usernameOrEmail']) && isset($userData['password'])) {
            $usernameOrEmail = $userData['usernameOrEmail'];
            $field = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $credentials = [
                $field => $usernameOrEmail,
                'password' => $userData['password']
            ];
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

//            this will delete the tokens form all devices for mobiles :) but keep the website work
            $user->tokens()->where('name', 'mobile')->delete();

            // CHeck If This User Verify Email
            if (!$user->hasVerifiedEmail()) {
                $token = $user->createToken('mobile')->accessToken;
                $tokenWebsite = $user->createToken('website')->accessToken;
                $user->token = $token;
                $user->token_website = $tokenWebsite;
                $user->verified_email = false;
                return new UserLoginResource($user);
            }

            if($user->status == 3)
            {
                throw new \Exception(__('validation.api.you-deactivated-by-admin-wait-to-unlock-the-block'));
            }
            if($user->status == 4)
            {
                throw new \Exception(__('validation.api.wait-for-admin-to-accept-your-application'));
            }

            $token = $user->createToken('mobile')->accessToken;
            $tokenWebsite = $user->createToken('website')->accessToken;
            $user->token = $token;
            $user->token_website = $tokenWebsite;
            $user->verified_email = true;

            $existingDeviceToken = DeviceToken::where('user_id', $user->id)->first();
            if ($existingDeviceToken) {
                $existingDeviceToken->update(['token' => $userData['device_token']]);
            } else {
                DeviceToken::create(['user_id' => $user->id, 'token' => $userData['device_token']]);
            }

            return new UserLoginResource($user);
        } else {
            throw new \Exception(__('validation.api.invalid-credentials'));
        }
    }

    public function logout()
    {
        //to get logout from all devices
        $user = Auth::guard('api')->user();
//        $user->tokens()->each(function ($token) {
//            $token->delete();
//        });

        //logout from the current device
        $userToken = $user->token();
        $userToken->delete();
    }

    public function deleteAccount()
    {
        //you should delete user from everywhere in the app check every place didnt has foreign key
        /** @var \App\Models\User $user */
        $user = Auth::guard('api')->user();
        $id = $user->id;
        $email = $user->email;
        $plan = Plan::where('creator_type', 'App\Models\User')->where('creator_id', $id)->exists();
        if ($plan) {
            $plans = Plan::where('creator_type', 'App\Models\User')->where('creator_id', $id)->get();
            foreach ($plans as $plan) {
                $plan->delete();
            }
        }

        $media = Media::where('model_type', 'App\Models\User')->where('model_id', $id)->exists();
        if ($media) {
            $media = Media::where('model_type', 'App\Models\User')->where('model_id', $id)->get();
            foreach ($media as $singleMedia) {
                $singleMedia->delete();
            }
        }

        DB::table('taggables')
            ->where('taggable_type', 'App\Models\User')
            ->where('taggable_id', $id)
            ->delete();
        $user = User::where('email', $email)->where('id', $id)->first();
        if ($user) {
            $user->delete();
        }
    }

    public function deactivateAccount()
    {
        $now = Carbon::now('Asia/Riyadh');

        $userId = Auth::guard('api')->user()->id;
        $user = User::find($userId);
        $user->status = 0;
        $user->save();

        // ======================= Deactivate User Trips =======================
        $userTrip = Trip::where('user_id', $userId)->latest()->first();
        if ($userTrip && $userTrip->date_time > $now) {
            $userTrip->status = 4;
            $userTrip->save();
            UsersTrip::where('trip_id', $userTrip->id)->update(['status' => 5]);
        }
        UsersTrip::where('user_id', $userId)->update(['status' => 5]);
        // ===================== End Deactivate User Trips ======================

    }
}
