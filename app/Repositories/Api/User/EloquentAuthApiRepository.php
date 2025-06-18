<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\GuideTrip;
use App\Models\GuideTripUser;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\Service;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Activity;
use mysql_xdevapi\Exception;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);
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

            $referralCode = $userData['referral_code'] ?? null;

            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
                if ($referrer) {
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_id' => $user->id,
                        'referral_code' => $referralCode
                    ]);
                }
            }

            // Notify an admin about the new user registration
            adminNotification(
                'New User Registered',
                "A new user ({$user->username}) (ID: {$user->id}) has just registered.",
                ['action' => 'view_user', 'action_label' => 'View User', 'action_url' => route('filament.admin.resources.users.index')]
            );

            $user->sendEmailVerificationNotification();
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

            if ($user->status == 3) {
                throw new \Exception(__('validation.api.you-deactivated-by-admin-wait-to-unlock-the-block'));
            }
            if ($user->status == 4) {
                throw new \Exception(__('validation.api.wait-for-admin-to-accept-your-application'));
            }

            if ($user->status == 0) {
                $user->status = 1;
                $user->save();
                $userTrips = Trip::where('user_id', $user->id)->latest()->get();
                if($userTrips){
                    foreach ($userTrips as $userTrip) {
                        if ($userTrip->date_time > $now && $userTrip->status == 4) {
                            $userTrip->status = 1;
                            $userTrip->save();
                            UsersTrip::where('trip_id', $userTrip->id)->where('status', 5)->update(['status' => 1]);
                        }
                    }
                }
                UsersTrip::where('user_id', $user->id)->where('status', 5)->update(['status' => 1]);
                //check if the user has active trip while deactivation and still active after activation
                $guideTrips = GuideTrip::where('guide_id', $user->id)->latest()->get();
                if ($guideTrips) {
                    foreach ($guideTrips as $guideTrip) {
                        if ($guideTrip->start_datetime > $now && $guideTrip->status == 4) {
                            $guideTrip->status = 1;
                            $guideTrip->save();
                            GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('status', 5)->update(['status' => 1]);
                        }
                    }

                }
                GuideTripUser::where('user_id', $user->id)->where('status', 5)->update(['status' => 1]);

                //check the services of the user while deactivation and after activation still in the time of active
                $services = Service::where('provider_type','App\Models\User')->where('provider_id',$user->id)->latest()->get();
                if ($services) {
                    foreach ($services as $service) {
                        if ($service->serviceBookings->available_end_date > $now && $service->status == 4) {
                            $service->status = 1;
                            $service->save();
                            //update the user who booked the service to 1
//                            ServiceReservation::where('service_id', $service->id)->where('status', 5)->update(['status' => 1]);
                        }
                    }
                }
                //update his booking
//                ServiceReservation::where('user_id', $user->id)->where('status', 5)->update(['status' => 1]);
            }

            //$user->tokens()->where('name', 'mobile')->delete();

            // CHeck If This User Verify Email
            if (!$user->hasVerifiedEmail()) {
                $token = $user->createToken('mobile')->accessToken;
                $tokenWebsite = $user->createToken('website')->accessToken;
                $user->token = $token;
                $user->token_website = $tokenWebsite;
                $user->verified_email = false;
                return new UserLoginResource($user);
            }

            $token = $user->createToken('mobile')->accessToken;
            $tokenWebsite = $user->createToken('website')->accessToken;
            $user->token = $token;
            $user->token_website = $tokenWebsite;
            $user->verified_email = true;

            if($userData['device_token']){
                $deviceToken = $userData['device_token'];

                $existing = DeviceToken::where('user_id', $user->id)
                    ->where('token', $deviceToken)
                    ->exists();

                if (!$existing) {
                    DeviceToken::create([
                        'user_id' => $user->id,
                        'token' => $deviceToken,
                    ]);
                }
            }


            $this->trackUserLoginVisit($user);
            activityLog('User', $user, 'the user logged in', 'login');

            return new UserLoginResource($user);
        } else {
            throw new \Exception(__('validation.api.invalid-credentials'));
        }
    }

    public function logout($deviceToken)
    {
        //to get logout from all devices
        $user = Auth::guard('api')->user();
        //logout from the current device
        $userToken = $user->token();
        $userToken->delete();
        $device = DeviceToken::where('token', $deviceToken)->where('user_id', $user->id)->first();
        $device->delete();
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

        $services  = Service::where('provider_type', 'App\Models\User')->where('provider_id', $id)->exists();
        if ($services) {
            $services = Service::where('provider_type', 'App\Models\User')->where('provider_id', $id)->get();
            foreach ($services as $service) {
                $service->delete();
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
        $userTrips = Trip::where('user_id', $userId)->latest()->get();

        if ($userTrips) {
            foreach ($userTrips as $userTrip) {
                if ($userTrip->date_time > $now && $userTrip->status == 1) {
                    $userTrip->status = 4;
                    $userTrip->save();
                    UsersTrip::where('trip_id', $userTrip->id)->where('status', 1)->update(['status' => 5]);
                }
            }
        }

        UsersTrip::where('user_id', $userId)->where('status', 1)->update(['status' => 5]);
        // ===================== End Deactivate User Trips ======================
        $guideTrips = GuideTrip::where('guide_id', $userId)->latest()->get();
        if ($guideTrips) {
            foreach ($guideTrips as $guideTrip) {
                if ($guideTrip->start_datetime > $now && $guideTrip->status == 1) {
                    $guideTrip->status = 4;
                    $guideTrip->save();
                    GuideTripUser::where('guide_trip_id', $guideTrip->id)->where('status', 1)->update(['status' => 5]);
                }
            }

        }
        GuideTripUser::where('user_id', $userId)->where('status', 1)->update(['status' => 5]);
        $services = Service::where('provider_type','App\Models\User')->where('provider_id',$user->id)->latest()->get();
        if ($services) {
            foreach ($services as $service) {
                if ($service->serviceBookings->available_end_date > $now && $service->status == 1) {
                    $service->status = 4;
                    $service->save();
                    //update the user who booked the service to 1
//                   ServiceReservation::where('service_id', $service->id)->where('status', 1)->update(['status' => 5]);
                }
            }
        }
        //update his booking
        //ServiceReservation::where('user_id', $user->id)->where('status', 1)->update(['status' => 5]);
    }

    private function trackUserLoginVisit($user): void
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $today = now()->toDateString();

        $visit = Visit::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->first();

        if ($visit) {
            if ($visit->ip_address !== $ip) {
                $visit->ip_address = $ip;
                $visit->save();
            }
        } else {
            $updated = Visit::whereNull('user_id')
                ->where('ip_address', $ip)
                ->whereDate('created_at', $today)
                ->update(['user_id' => $user->id]);

            if ($updated === 0) {
                Visit::create([
                    'user_id' => $user->id,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'platform' => php_uname('s'),
                ]);
            }
        }
    }
}
