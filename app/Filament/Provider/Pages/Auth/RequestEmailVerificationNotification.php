<?php

namespace App\Filament\Provider\Pages\Auth;

use App\Notifications\Users\UserEmailVerificationNotification;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Pages\Page;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;


class RequestEmailVerificationNotification extends BaseEmailVerificationPrompt
{
    protected function sendEmailVerificationNotification(MustVerifyEmail $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = app(VerifyEmail::class);
//        $notification->url = Filament::getVerifyEmailUrl($user);
        $notification = new UserEmailVerificationNotification();

        $user->notify($notification);
    }
}
