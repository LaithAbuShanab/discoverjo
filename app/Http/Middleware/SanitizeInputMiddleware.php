<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInputMiddleware
{
    // List of route names or URIs to exclude
    protected $excludedRoutes = [
        'login',
        'register',
        'logout',
        'verification.verify',
        'verification.send',
        'password.email',
        'password.reset',
        'password.store',
        'change-password',

        // Direct URI paths if route names aren't used
        'auth/*',
        'email/verify/*',
        'email/verification-notification',
        'forgot-password',
        'user/reset-password/*',
        'reset-password',
    ];

    public function handle(Request $request, Closure $next)
    {


        foreach ($this->excludedRoutes as $excluded) {
            if ($request->is($excluded)) {
                return $next($request);
            }
        }

        // Sanitize all inputs
        $sanitized = $this->sanitizeRecursive($request->all());
        $request->merge($sanitized);
        return $next($request);
    }

    protected function sanitizeRecursive($data)
    {

        return collect($data)->map(function ($value) {
            if (is_array($value)) {
                return $this->sanitizeRecursive($value);
            }
            return cleanQuery($value);
        })->toArray();
    }
}
