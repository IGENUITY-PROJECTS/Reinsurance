<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\PortalAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAccessController extends Controller
{
    public function update(Request $request, int $id, PortalAccounts $accounts)
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);
        $input = $request->validate(['portal_user_limit' => ['required', 'integer', 'min:1', 'max:100']]);
        DB::transaction(function () use ($id, $input, $accounts) {
            $company = Company::cedants()->lockForUpdate()->findOrFail($id);
            if ($accounts->reserved($company)->count() > $input['portal_user_limit']) {
                throw ValidationException::withMessages(['portal_user_limit' => 'The limit cannot be lower than the current approved and live pending accounts.']);
            }
            $company->portal_user_limit = $input['portal_user_limit'];
            $company->save();
        });

        return back()->with('status', 'Company user limit updated.');
    }
}
