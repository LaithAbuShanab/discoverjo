<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->hasVerifiedEmail() && !$request->is('email/verify', 'email/verify/*', 'logout')) {
            return ApiResponse::sendResponse(403,__('validation.api.you-should-verify-email-first'),[]);
        }

        return $next($request);
    }
}
