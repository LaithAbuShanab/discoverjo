<?php

use App\Models\GroupMember;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Response;
use App\Helpers\ApiResponse;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('new-user', function () {
    return true; // Allow all users to access this channel
}, ['guards' => ['admin']]);

use Symfony\Component\HttpKernel\Exception\HttpException;

Broadcast::channel('group-channel.{id}', function ($user, $conversationId) {
    $isAuthorized = GroupMember::where([
        'conversation_id' => $conversationId,
        'user_id' => $user->id
    ])->exists();

    if (!$isAuthorized) {
        return ApiResponse::sendResponseError(Response::HTTP_UNAUTHORIZED, 'you cannot start chat  because your are not a member in this trip');
    }

    return true;
}, ['guards' => ['api']]);
