<?php

namespace App\Providers;

use App\Http\Middleware\EnsurePortalApproved;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->symbols()->rules(['regex:/[\p{S}\p{P}]/u']));

        Livewire::addPersistentMiddleware([EnsureEmailIsVerified::class, EnsurePortalApproved::class, RoleMiddleware::class, PermissionMiddleware::class, RoleOrPermissionMiddleware::class]);

        Gate::before(function ($user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
