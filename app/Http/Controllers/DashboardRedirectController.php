<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasAnyRole(['super-admin', 'admin']) || $user->can('view admin dashboard')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        }

        abort(403, 'Your account does not have an assigned dashboard role.');
    }
}
