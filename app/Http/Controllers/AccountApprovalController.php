<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\PortalAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountApprovalController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->access_status === 'approved') {
            return redirect()->route('dashboard');
        }

        return view('auth.pending-approval', ['user' => $request->user()]);
    }

    public function renew(Request $request, PortalAccounts $accounts)
    {
        DB::transaction(function () use ($request, $accounts) {
            $company = Company::lockForUpdate()->findOrFail($request->user()->company_id);
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($user->hasRole('client') && $user->access_status === 'pending' && $user->access_expires_at?->isPast(), 403);
            $accounts->assertCapacity($company, $user->id);
            $user->access_expires_at = now()->addDays(7);
            $user->save();
        });

        return back()->with('status', 'Your approval request has been renewed. Please contact your broker to confirm your membership.');
    }
}
