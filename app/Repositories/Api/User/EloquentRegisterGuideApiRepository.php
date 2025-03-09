<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\RegisterGuideApiRepositoryInterface;
use App\Models\Admin;
use App\Models\DeviceToken;
use App\Models\Plan;
use App\Models\Tag;
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
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;


class EloquentRegisterGuideApiRepository implements RegisterGuideApiRepositoryInterface
{
    public function register($userData,$token,$tags,$userImage,$userFile)
    {
        DB::beginTransaction();
        try {
            $user = User::create($userData);
            $device_token = new DeviceToken();
            $device_token->user_id = $user->id;
            $device_token->token = $token;
            $device_token->save();

            $tagIds = Tag::whereIn('slug', $tags)->pluck('id');
            $user->tags()->sync($tagIds);

            if ($userImage !== null) {
                $extension = pathinfo($userImage->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $user->addMediaFromRequest('image')->usingFileName($filename)->toMediaCollection('avatar');
            }
            if ($userFile) {
                $file =$userFile;
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg+xml', 'image/webp'])) {
                    $user->addMediaFromRequest('professional_file')->usingFileName($filename)->toMediaCollection('file');
                } elseif ($file->getMimeType() === 'application/pdf') {
                    $user->addMediaFromRequest('professional_file')->usingFileName($filename)->toMediaCollection('file');
                }
            }
            event(new Registered($user));
            Notification::send(Admin::all(), new NewUserRegisteredNotification($user));
                DB::commit();
    //        Http::post('http://127.0.0.1:3000/notifications');
            return (new UserResource($user));
        } catch (\Exception $e) {
            // If there is an error, rollback the transaction
            DB::rollback();

            throw new HttpException(404,$e->getMessage());

        }
    }


}
