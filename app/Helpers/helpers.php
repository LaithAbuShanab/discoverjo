<?php

use App\Models\Admin;
use App\Models\DeleteCounter;
use App\Notifications\Users\Warning\NewWarningUserNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Http;


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

function activityLog($logName, $model, $description, $event, $extraProps = [])
{
    $activity = activity($logName)
        ->causedBy(Auth::guard('api')->check() ? Auth::guard('api')->user() : null)
        ->withProperties(array_merge([
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ], $extraProps));

    $activity->event($event);

    if ($model) {
        $activity->performedOn($model);
    }

    $activity->log($description);
}

function adminNotification($title = null, $body = null, $options = [])
{
    $recipient = Admin::all();
    if ($recipient) {
        Notification::make()
            ->title($title)
            ->success()
            ->body($body)
            ->actions([
                Action::make($options['action'])
                    ->label($options['action_label'])
                    ->url($options['action_url']),
            ])
            ->sendToDatabase($recipient);
    }
}

function handleWarning(object $record): void
{
    $latestCount = DeleteCounter::where('user_id', $record->user_id)
        ->latest('created_at')
        ->value('deleted_count') ?? 0;

    DeleteCounter::create([
        'typeable_type' => get_class($record),
        'typeable_id'   => $record->id,
        'user_id'       => $record->user_id,
        'deleted_count' => $latestCount + 1,
    ]);

    $totalWarnings = $latestCount + 1;
    $user = $record->user;
    $deviceToken = optional($user->deviceToken)->token;
    $receiverLanguage = in_array($user->lang, ['en', 'ar']) ? $user->lang : 'en';

    if ($totalWarnings === 4) {
        $user->status = 0;
        $user->save();

        // Insert into blocked_users table
        DB::table('blocked_users')->insert([
            'email' => $user->email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        FacadesNotification::send($user, new NewWarningUserNotification('blacklisted'));

        if ($deviceToken) {
            $notificationData = [
                'title' => Lang::get('app.notifications.new-blacklisted-title', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-blacklisted-body', ['username' => $user->username], $receiverLanguage),
                'sound' => 'default',
            ];
            sendNotification([$deviceToken], $notificationData);
        }
    } elseif ($totalWarnings >= 3) {
        $user->status = 0;
        $user->save();

        FacadesNotification::send($user, new NewWarningUserNotification('blocked'));

        if ($deviceToken) {
            $notificationData = [
                'title' => Lang::get('app.notifications.new-blocked-two-weeks-title', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-blocked-two-weeks-body', ['username' => $user->username], $receiverLanguage),
                'sound' => 'default',
            ];
            sendNotification([$deviceToken], $notificationData);
        }
    } else {
        FacadesNotification::send($user, new NewWarningUserNotification('warning'));

        if ($deviceToken) {
            $notificationData = [
                'title' => Lang::get('app.notifications.new-warning-title', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-warning-body', ['username' => $user->username], $receiverLanguage),
                'sound' => 'default',
            ];
            sendNotification([$deviceToken], $notificationData);
        }
    }
}

function getAddressFromCoordinates(float $lat, float $lng, string $language): string
{
    // Validate input coordinates
    if (!is_numeric($lat) || !is_numeric($lng) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return 'Invalid Coordinates';
    }

    $apiKey = config('app.GOOGLE_API_KEY');

    try {
        $response = Http::timeout(10)->retry(3, 100)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng'   => "{$lat},{$lng}",
            'language' => $language,
            'key'      => $apiKey,
        ]);

        if (!$response->successful()) {
            return 'API Request Failed';
        }

        $data = $response->json();

        if (
            !isset($data['results'][0]['address_components']) ||
            empty($data['results'][0]['address_components'])
        ) {
            return 'No Address Components Found';
        }

        $components = $data['results'][0]['address_components'];
        $locality = '';
        $subLocality = '';

        foreach ($components as $component) {
            $types = $component['types'];

            if (in_array('sublocality', $types) || in_array('sublocality_level_1', $types)) {
                $subLocality = $component['long_name'];
            }

            if (in_array('locality', $types)) {
                $locality = $component['long_name'];
            }

            // Early exit if both are found
            if ($locality && $subLocality) {
                break;
            }
        }

        $result = trim("{$subLocality}, {$locality}", ', ');
        return $result !== '' ? $result : 'Address Not Found';

    } catch (\Exception $e) {
        // You could log the exception here if needed
        return 'Error Fetching Address';
    }
}

