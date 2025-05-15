<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $today = now()->toDateString();

        $visitorKey = sha1($ip . '|' . $userAgent . '|' . $today);

        $notVisitedToday = Cache::add('visit_' . $visitorKey, true, now()->addDay());

        if ($notVisitedToday) {
            Visit::create([
                'user_id' => auth()->guard('api')->user()->id ?? null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'platform' => php_uname('s'),
            ]);
        }

        return $next($request);
    }
}
