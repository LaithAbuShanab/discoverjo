<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Facades\Filament;

class RegisterResponse implements RegistrationResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (Filament::getCurrentPanel()->getId() === 'provider') {
            return redirect()->route('filament.provider.auth.login');
        } elseif (Filament::getCurrentPanel()->getId() === 'host') {
            return redirect()->route('filament.host.auth.login');
        }

        return redirect()->route('filament.guide.auth.login');
    }
}
