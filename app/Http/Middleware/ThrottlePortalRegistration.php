<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottlePortalRegistration
{
    public function handle(Request $request, Closure $next)
    {
        return $request->routeIs('register.store')
            ? app(ThrottleRequests::class)->handle($request, $next, 'registration')
            : $next($request);
    }
}
