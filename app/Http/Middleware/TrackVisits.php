<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
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
        $today = Carbon::today();

        $alreadyVisited = Visit::whereDate('created_at', $today)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('ip_address', $ip))
            ->exists();

        if (! $alreadyVisited) {
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
