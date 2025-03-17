<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use App\Models\Admin;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use App\Models\Plan;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class EloquentAuthApiRepository implements AuthApiRepositoryInterface
{
    public function register(array $userData)
    {
        return DB::transaction(function () use ($userData) {
            // Extract and remove device token
            $token = $userData['device_token'] ?? null;
            unset($userData['device_token']);

            // Create the user
            $user = User::create($userData);

            // Save device token if available
            if ($token) {
                DeviceToken::create([
                    'user_id' => $user->id,
                    'token'   => $token,
                ]);
            }

            // Auto-follow a specific user (e.g., user ID 1)
            Follow::create([
                'following_id' => 1,
                'follower_id'  => $user->id,
                'status'       => 1,
            ]);

            // Notify an admin about the new user registration
            $recipient = Admin::where('email', 'asma.abughaith@gmail.com')->first();
            if ($recipient) {
                Notification::make()
                    ->title('New User Registered')
                    ->success()
                    ->body("A new user ({$user->username}) (ID: {$user->id}) has just registered.")
                    ->actions([
                        Action::make('view_user')
                            ->label('View User')
                            ->url(route('filament.admin.resources.users.index')),
                    ])
                    ->sendToDatabase($recipient);
            }

            return new UserResource($user);
        });
    }
    public function login($userData)
    {
        $now = Carbon::now('Asia/Riyadh');
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

            if ($user->status == 3) {
                throw new \Exception(__('validation.api.you-deactivated-by-admin-wait-to-unlock-the-block'));
            }
            if ($user->status == 4) {
                throw new \Exception(__('validation.api.wait-for-admin-to-accept-your-application'));
            }
            if($user->status == 0){
                $user->status = 1;
                $user->save();
                $userTrip = Trip::where('user_id', $user->id)->latest()->first();
                if($userTrip){
                    if ( $userTrip->date_time > $now && $userTrip->status ==4) {
                        $userTrip->status = 1;
                        $userTrip->save();
                        UsersTrip::where('trip_id', $userTrip->id)->where('status',5)->update(['status' => 1]);
                    }
                }
                UsersTrip::where('user_id', $user->id)->where('status',5)->update(['status' => 1]);

                $guideTrip = GuideTrip::where('guide_id', $user->id)->latest()->first();
                if($guideTrip){
                    if ( $guideTrip->start_datetime > $now && $guideTrip->status ==4) {
                        $guideTrip->status = 1;
                        $guideTrip->save();
                        GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('status',5)->update(['status' => 1]);
                    }
                }
                GuideTripUser::where('user_id', $user->id)->where('status',5)->update(['status' => 1]);
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
            activityLog('User',$user,'the user logged in','login');

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

        //we shouldn't delete trip and guide trip because they connected to post of user so we should put it to User System
        if ($user) {
            $user->delete();
        }
    }

    public function deactivateAccount()
    {
        //when the trip & guide trip status 4 when the user deactivate && the user in those trip become 5 when they acceptance and deactivate trips
        $now = Carbon::now('Asia/Riyadh');

        $userId = Auth::guard('api')->user()->id;
        $user = User::find($userId);
        $user->status = 0;
        $user->save();

        // ======================= Deactivate User Trips =======================
        $userTrip = Trip::where('user_id', $userId)->latest()->first();
        if($userTrip){
            if ( $userTrip->date_time > $now && $userTrip->status ==1) {
                $userTrip->status = 4;
                $userTrip->save();
                UsersTrip::where('trip_id', $userTrip->id)->where('status',1)->update(['status' => 5]);
            }
        }

        UsersTrip::where('user_id', $userId)->where('status',1)->update(['status' => 5]);
        // ===================== End Deactivate User Trips ======================
        $guideTrip = GuideTrip::where('guide_id', $userId)->latest()->first();
        if($guideTrip){
            if ( $guideTrip->start_datetime > $now && $guideTrip->status ==1) {
                $guideTrip->status = 4;
                $guideTrip->save();
                GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('status',1)->update(['status' => 5]);
            }
        }
        GuideTripUser::where('user_id', $userId)->where('status',1)->update(['status' => 5]);
    }
}
