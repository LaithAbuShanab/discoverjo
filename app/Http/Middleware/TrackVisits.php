<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->guard('api')->user()->id ?? null;
        $ip = $request->ip();
        $today = now()->toDateString();

        $cacheKey = 'visit_' . ($userId ?? $ip) . '_' . $today;

        $notVisitedToday = Cache::add($cacheKey, true, now()->addDay());

        if ($notVisitedToday) {
            Visit::create([
                'user_id' => $userId,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'platform' => php_uname('s'),
            ]);
        }

        return $next($request);
    }

}
