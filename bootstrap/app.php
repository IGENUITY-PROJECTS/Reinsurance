<?php

use App\Http\Middleware\EnsurePortalApproved;
use App\Http\Middleware\ThrottlePortalRegistration;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [ThrottlePortalRegistration::class]);
        $middleware->alias([
            'portal.approved' => EnsurePortalApproved::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $exception, $request) {
            if ($response->getStatusCode() !== 403 || $request->expectsJson()) {
                return $response;
            }
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            $redirect = redirect()->route('login', [], 303);
            if ($request->hasSession()) {
                $redirect->with('access_denied', 'You do not have access to that page. Please sign in with the appropriate account.');
            }

            return $redirect;
        });
    })->create();
