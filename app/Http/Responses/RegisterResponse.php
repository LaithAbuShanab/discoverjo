<?php

namespace App\Http\Responses;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;



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
        // Determine URL based on request path
        if ( Str::contains(url()->previous(), 'provider')) {
            $url = '/provider/login';
        } else {
            $url = '/guide/login';
        }

        return redirect()->intended($url);
    }


}
