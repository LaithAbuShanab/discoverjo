<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        if ($user && in_array($user->status, [3, 4])) {
            return ApiResponse::sendResponse(403, __('Your-still-inactive'), []);
        }

        return $next($request);
    }
}
