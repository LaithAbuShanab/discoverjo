<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\Users\UserEmailVerificationNotification;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->hasVerifiedEmail()) {
            return response(['message' => __('app.api.email-already-verified')]);
        }
        $user->notify(new UserEmailVerificationNotification());
        return response()->json([
            'message' => __('app.api.email-sent-successfully'),
        ], 200);
    }
}
