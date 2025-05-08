<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\RegisterGuideApiRepositoryInterface;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LevelUp\Experience\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EloquentRegisterGuideApiRepository implements RegisterGuideApiRepositoryInterface
{
    public function register($userData, $token, $tags, $userImage, $userFile)
    {
        DB::beginTransaction();
        try {
            $user = User::create($userData);
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);
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
                $file = $userFile;
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg+xml', 'image/webp'])) {
                    $user->addMediaFromRequest('professional_file')->usingFileName($filename)->toMediaCollection('file');
                } elseif ($file->getMimeType() === 'application/pdf') {
                    $user->addMediaFromRequest('professional_file')->usingFileName($filename)->toMediaCollection('file');
                }
            }

            Follow::create([
                'following_id' => 1,
                'follower_id'  => $user->id,
                'status'       => 1,
            ]);
            adminNotification(
                'New Guide Registered',
                "A new guide ({$user->username}) (ID: {$user->id}) has just registered.",
                ['action' => 'view_user', 'action_label' => 'View User', 'action_url' => route('filament.admin.resources.guides.index')]
            );

            DB::commit();
            return (new UserResource($user));
        } catch (\Exception $e) {
            DB::rollback();
            throw new HttpException(404, $e->getMessage());
        }
    }
}
