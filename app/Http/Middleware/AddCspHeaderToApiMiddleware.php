<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCspHeaderToApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/*')) {
            $response->headers->set('Content-Security-Policy',
                "default-src 'none'; " .
                "img-src 'self' https://your-bucket.s3.amazonaws.com data:; " .
                "style-src 'self' 'unsafe-inline'; " .
                "font-src 'self'; " .
                "frame-src 'none'; " .
                "script-src 'none';"
            );

        }

        return $response;
    }
}
