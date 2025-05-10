<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordReset
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('admin')->check() && auth()->guard('admin')->user()?->must_reset_password) {
            if (!$request->routeIs('filament.admin.pages.reset-initial-password')) {
                return redirect()->route('filament.admin.pages.reset-initial-password');
            }
        }

        return $next($request);
    }
}
