<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Trip;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTripStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tripSlug = $request->trip_slug ?? null;

        if ($tripSlug) {
            $trip = Trip::where('slug', $tripSlug)->first();

            if ($trip && $trip->user->status != 1) {
                return ApiResponse::sendResponseError(403, __('app.we-are-sorry-this-trip-is-no-longer-available'));
            }
        }

        return $next($request);
    }
}
