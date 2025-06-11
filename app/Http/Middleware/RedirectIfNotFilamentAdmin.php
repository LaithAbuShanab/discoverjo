<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;


class RedirectIfNotFilamentAdmin  extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected function authenticate($request, array $guards): void
    {
        $auth = Filament::auth();
        $user = $auth->user();
        $panel = Filament::getCurrentPanel();

        if (!($auth->check()
            && $user instanceof FilamentUser
            && $user->canAccessPanel($panel))) {
            Session::flush();
            $this->unauthenticated($request, $guards);
        }
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
