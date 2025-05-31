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

        $notVisitedToday = Cache::add('visitsv2_' . $visitorKey, true, now()->addDay());

        if ($notVisitedToday) {
            $user = auth()->guard('api')->user();

            $alreadyVisited = Visit::query()
                ->when($user, function ($query) use ($user, $today) {
                    return $query->where('user_id', $user->id)
                                 ->whereDate('created_at', $today);
                }, function ($query) use ($ip, $today) {
                    return $query->whereNull('user_id')
                                 ->where('ip_address', $ip)
                                 ->whereDate('created_at', $today);
                })
                ->exists();

            if (! $alreadyVisited) {
                Visit::create([
                    'user_id' => $user->id ?? null,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'platform' => php_uname('s'),
                ]);
            }
        }

        return $next($request);
    }

}
