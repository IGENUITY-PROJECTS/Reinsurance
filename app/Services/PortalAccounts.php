<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PortalAccounts
{
    public function reserved(Company $company): Builder
    {
        return User::where('company_id', $company->id)->where(fn ($q) => $q->where('access_status', 'approved')
            ->orWhere(fn ($pending) => $pending->where('access_status', 'pending')->where('access_expires_at', '>', now())));
    }

    // Caller holds the company row lock inside its transaction, including approval and account moves.
    public function assertCapacity(Company $company, ?int $ignore = null, string $field = 'company_code'): void
    {
        if ($this->reserved($company)->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->count() >= $company->portal_user_limit) {
            throw ValidationException::withMessages([$field => 'This company has reached its portal user limit. Please contact your broker.']);
        }
    }
}
