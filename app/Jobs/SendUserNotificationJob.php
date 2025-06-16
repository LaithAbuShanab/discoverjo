<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Users\SendNotificationFromAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // ✅ Required
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class SendUserNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // ✅ Dispatchable is required

    public array $title;
    public array $body;
    public array $userIds;

    public function __construct(array $title, array $body, array $userIds)
    {
        $this->title = $title;
        $this->body = $body;
        $this->userIds = $userIds;
    }

    public function handle(): void
    {
        $userModels = User::whereIn('id', $this->userIds)->get();

        // Laravel Notification
        FacadesNotification::send($userModels, new SendNotificationFromAdmin($this->title, $this->body));

        // Firebase
        foreach ($userModels as $user) {
            if ($user->DeviceTokenMany && $user->DeviceTokenMany->isNotEmpty()) {
                $lang = $user->lang ?? 'en';
                $tokens = $user->DeviceTokenMany->pluck('token')->toArray();

                if (!empty($tokens)) {
                    sendNotification($tokens, ['notification' => [
                        'title' => $this->title[$lang],
                        'body' => $this->body[$lang],
                        'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                        'sound' => 'default',
                    ], 'data' => []]);
                }
            }
        }
    }
}
