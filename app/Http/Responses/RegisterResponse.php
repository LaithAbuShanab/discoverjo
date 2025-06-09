<?php

namespace App\Http\Responses;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;


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
        // return whatever you want as url
        $url = '/guide/login';

        return redirect()->intended($url);
    }

}
