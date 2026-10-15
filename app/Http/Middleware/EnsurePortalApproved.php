<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePortalApproved
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->access_status !== 'approved') {
            return $request->expectsJson() ? response()->json(['message' => 'Your account is awaiting approval or is inactive.'], 403)
                : redirect()->route('account.pending');
        }

        return $next($request);
    }
}
