<?php

use App\Models\Admin;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

function AdminPermission($permission)
{
    return Auth::guard('admin')->user()->hasAnyPermission($permission);
}

function getLang()
{
    return Auth::guard('admin')->user()->lang;
}

function businessStatusTranslation($lang, $request)
{
    $business_status_translation = [
        'en' => [
            0 => 'Closed',
            1 => 'Operational',
            2 => 'Temporary Closed',
            3 => 'We don\'t have any information about that'
        ],
        'ar' => [
            0 => 'مغلق',
            1 => 'شغال',
            2 => 'مغلق مؤقتا',
            3 => 'ليس لدينا معلومة عن ذلك'

        ],
    ];
    return $business_status_translation[$lang][$request];
}

function haversineDistance($userLat, $userLng, $placeLat, $placeLng)
{
    $earthRadius = 6371;

    $latDifference = deg2rad($placeLat - $userLat);
    $lngDifference = deg2rad($placeLng - $userLng);

    $a = sin($latDifference / 2) * sin($latDifference / 2) +
        cos(deg2rad($userLat)) * cos(deg2rad($placeLat)) *
        sin($lngDifference / 2) * sin($lngDifference / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return $distance;
}

function daysTranslation($lang, $request)
{
    $dayTranslation = [
        'en' => [
            'Sunday' => 'Sunday',
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
        ],
        'ar' => [
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ],
    ];

    return $dayTranslation[$lang][$request];
}

function dateTime($dateTime)
{
    $timeFormat = new \DateTime($dateTime);
    return $timeFormat;
}

function sendNotification($deviceTokens, $data)
{
    // Load Firebase credentials
    $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
    $messaging = $factory->createMessaging();

    // Build the notification message
    $notification = FirebaseNotification::create($data['title'], $data['body']);
    $message = CloudMessage::new()->withNotification($notification)->withData($data);

    if (is_array($deviceTokens)) {
        $response = $messaging->sendMulticast($message, $deviceTokens);
    } else {
        $response = $messaging->send($message->withChangedTarget('token', $deviceTokens));
    }

    return $response;
}

function activityLog($logName, $model, $description, $event)
{
    $activity = activity($logName)
        ->causedBy(Auth::guard('api')->check() ? Auth::guard('api')->user() : null)
        ->withProperties([
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    $activity->event($event);
    if ($model) {
        $activity->performedOn($model);
    }

    $activity->log($description);
}

function adminNotification($user)
{
    $recipient = Admin::all();
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
}
