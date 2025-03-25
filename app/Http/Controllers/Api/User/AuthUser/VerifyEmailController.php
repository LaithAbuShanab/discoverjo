<?php

namespace App\Http\Controllers\Api\User\AuthUser;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Http\Requests\Api\User\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {

            $message = __('app.api.you-have-already-verify-your-email');
            return view('users.auth.verify_email',compact('message'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $message =__('app.api.you-have-verify-your-email-successfully');
        return view('users.auth.verify_email',compact('message'));
    }
}
