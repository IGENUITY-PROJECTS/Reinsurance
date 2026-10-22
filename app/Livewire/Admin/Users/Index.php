<?php

namespace App\Livewire\Admin\Users;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Notifications\PortalAccessChanged;
use App\Services\PortalAccounts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Throwable;

class Index extends Component
{
    use WithPagination;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $companyCode = '';

    public array $selectedRoles = [];

    public ?int $editingUserId = null;

    public ?int $departmentId = null;

    public bool $showEditor = false;

    public string $search = '';

    public string $accessFilter = '';

    public string $roleFilter = '';

    public string $companySearch = '';

    public string $tab = 'users';

    public function create(): void
    {
        $this->authorizeManager();
        $this->resetForm();
        $this->selectedRoles = ['client'];
        $this->showEditor = true;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'accessFilter', 'roleFilter'])) {
            $this->resetPage();
        }
        if ($property === 'companySearch') {
            $this->resetPage('companies_page');
        }
    }

    private function authorizeManager(): void
    {
        Gate::authorize('manage users');
        abort_unless(auth()->user()->hasAnyRole(['admin', 'super-admin']), 403);
    }

    public function mount(): void
    {
        $this->authorizeManager();
        $input = request()->validate(['tab' => 'nullable|in:users,limits']);
        $this->tab = $input['tab'] ?? 'users';
        abort_if($this->tab === 'limits' && ! auth()->user()->hasRole('super-admin'), 403);
    }

    private function assertCanManage(User $user): void
    {
        abort_unless(auth()->user()->hasRole('super-admin') || ($user->hasRole('client') && ! $user->hasAnyRole(['admin', 'super-admin'])), 403, 'Only the superadmin manages broker staff.');
    }

    public function save(): void
    {
        $this->authorizeManager();
        if ($this->editingUserId) {
            $this->assertCanManage(User::findOrFail($this->editingUserId));
        }
        $super = auth()->user()->hasRole('super-admin');
        $cedant = in_array('client', $this->selectedRoles, true);
        $staff = in_array('admin', $this->selectedRoles, true);
        $this->companyCode = trim($this->companyCode);
        $this->email = mb_strtolower(trim($this->email));
        $input = $this->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->editingUserId)],
            'password' => [$this->editingUserId ? 'nullable' : 'required', 'string', Password::default()],
            'selectedRoles' => ['required', 'array', 'size:1'],
            'selectedRoles.*' => ['required', Rule::in($super ? ['admin', 'client', 'super-admin'] : ['client']), Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
            'companyCode' => [Rule::excludeIf(! $cedant), 'required', 'string', 'max:50', Rule::exists('companies', 'CmpCode')->where('CmpSubTypeCode', '100')],
            'departmentId' => [Rule::excludeIf(! $staff), 'required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)],
        ]);
        $user = DB::transaction(function () use ($cedant, $staff, $input) {
            $company = $cedant ? Company::cedants()->where('CmpCode', $this->companyCode)->lockForUpdate()->firstOrFail() : null;
            $user = $this->editingUserId ? User::lockForUpdate()->findOrFail($this->editingUserId) : new User;
            if ($user->exists) {
                $this->assertCanManage($user);
            }
            if ($company && (! $user->exists || $user->access_status === 'approved' || $user->access_expires_at?->isFuture())) {
                app(PortalAccounts::class)->assertCapacity($company, $user->id, 'companyCode');
            }
            $emailChanged = ! $user->exists || $user->email !== $this->email;
            $user->fill(['name' => $this->name, 'email' => $this->email]);
            if ($this->password !== '') {
                $user->password = $this->password;
            }
            if ($emailChanged) {
                $user->email_verified_at = null;
            }
            if (! $user->exists || ! $cedant) {
                $user->access_status = 'approved';
                $user->approved_by = auth()->id();
                $user->approved_at = now();
                $user->access_expires_at = null;
            }
            $user->company()->associate($company);
            $user->department_id = $staff ? $input['departmentId'] : null;
            $user->save();
            $user->syncRoles($this->selectedRoles);

            return $user;
        });
        $status = 'User saved successfully.';
        if (! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
                $status .= ' A verification email has been queued.';
            } catch (Throwable $error) {
                report($error);
                $status .= ' The verification email could not be queued. The user can resend it after signing in.';
            }
        }
        $this->resetForm();
        session()->flash('status', $status);
    }

    public function setAccess(int $id, string $status): void
    {
        $this->authorizeManager();
        abort_unless(in_array($status, ['approved', 'rejected', 'suspended'], true), 422);
        $target = User::findOrFail($id);
        $this->assertCanManage($target);
        if (! $target->hasRole('client')) {
            abort_unless(auth()->user()->hasRole('super-admin') && $target->hasAnyRole(['admin', 'super-admin']) && in_array($status, ['approved', 'suspended'], true), 403);
            abort_if((int) $target->id === (int) auth()->id(), 403, 'You cannot change your own access status.');
            if ($status === 'approved' && ! $target->hasVerifiedEmail()) {
                throw ValidationException::withMessages(['approval' => 'The user must verify their email before activation.']);
            }
            $target = DB::transaction(function () use ($id, $status) {
                $staff = User::lockForUpdate()->findOrFail($id);
                abort_unless($staff->hasAnyRole(['admin', 'super-admin']) && ! $staff->hasRole('client'), 409);
                if ($status === 'approved' && ! $staff->hasVerifiedEmail()) {
                    throw ValidationException::withMessages(['approval' => 'The user must verify their email before activation.']);
                }
                $staff->forceFill(['access_status' => $status, 'approved_by' => auth()->id(), 'approved_at' => now()])->save();

                return $staff;
            });
            try {
                $target->notify(new PortalAccessChanged($status));
            } catch (Throwable $error) {
                report($error);
            }
            session()->flash('status', 'Staff access updated.');

            return;
        }
        DB::transaction(function () use ($target, $status) {
            $company = Company::cedants()->lockForUpdate()->findOrFail($target->company_id);
            $user = User::lockForUpdate()->findOrFail($target->id);
            $this->assertCanManage($user);
            abort_unless((int) $user->company_id === (int) $company->id, 409);
            if ($status === 'approved') {
                if (! $user->hasVerifiedEmail()) {
                    throw ValidationException::withMessages(['approval' => 'The user must verify their email before approval.']);
                }
                app(PortalAccounts::class)->assertCapacity($company, $user->id, 'approval');
                $user->approved_by = auth()->id();
                $user->approved_at = now();
            }
            $user->access_status = $status;
            $user->access_expires_at = null;
            $user->save();
        });
        $user = User::findOrFail($id);
        if ($user->hasVerifiedEmail()) {
            try {
                $user->notify(new PortalAccessChanged($status));
            } catch (Throwable $error) {
                report($error);
            }
        }
        session()->flash('status', 'Account access updated.');
    }

    public function edit(int $id): void
    {
        $this->authorizeManager();
        $user = User::findOrFail($id);
        $this->assertCanManage($user);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles()->pluck('name')->all();
        $this->companyCode = $user->company?->CmpCode ?? '';
        $this->departmentId = $user->department_id;
        $this->showEditor = true;
    }

    public function delete(int $id): void
    {
        $this->authorizeManager();
        abort_if((int) auth()->id() === $id, 403);
        DB::transaction(function () use ($id) {
            $user = User::lockForUpdate()->findOrFail($id);
            $this->assertCanManage($user);
            $user->delete();
        });
        if ($this->editingUserId === $id) {
            $this->resetForm();
        } $this->resetPage();
        session()->flash('status', 'User deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'companyCode', 'selectedRoles', 'editingUserId', 'departmentId', 'showEditor']);
        $this->resetValidation();
    }

    public function render()
    {
        $this->authorizeManager();
        $super = auth()->user()->hasRole('super-admin');
        if ($this->editingUserId) {
            $this->assertCanManage(User::findOrFail($this->editingUserId));
        }
        abort_unless(in_array($this->tab, ['users', 'limits'], true) && ($this->tab !== 'limits' || $super), 403);
        $visible = User::query()->when(! $super, fn ($q) => $q->role('client')->whereDoesntHave('roles', fn ($role) => $role->whereIn('name', ['admin', 'super-admin'])));
        $stats = ['total' => (clone $visible)->count(), 'approved' => (clone $visible)->where('access_status', 'approved')->count(),
            'pending' => (clone $visible)->where('access_status', 'pending')->count(), 'unverified' => (clone $visible)->whereNull('email_verified_at')->count()];
        $query = clone $visible;
        $search = mb_substr(trim($this->search), 0, 150);
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')
                ->orWhereHas('company', fn ($company) => $company->where('CompanyName', 'like', '%'.$search.'%')->orWhere('CmpCode', 'like', '%'.$search.'%')));
        }
        if (in_array($this->accessFilter, ['pending', 'approved', 'suspended', 'rejected'], true)) {
            $query->where('access_status', $this->accessFilter);
        }
        if ($super && in_array($this->roleFilter, ['client', 'admin', 'super-admin'], true)) {
            $query->role($this->roleFilter);
        }
        $companies = $super && $this->tab === 'limits' ? Company::query()
            ->when(trim($this->companySearch) !== '', fn ($q) => $q->where(fn ($q) => $q->where('CompanyName', 'like', '%'.mb_substr(trim($this->companySearch), 0, 150).'%')->orWhere('CmpCode', 'like', '%'.mb_substr(trim($this->companySearch), 0, 150).'%')))
            ->withCount(['users as reserved_user_count' => fn ($q) => $q->where(fn ($q) => $q->where('access_status', 'approved')->orWhere(fn ($q) => $q->where('access_status', 'pending')->where('access_expires_at', '>', now())))])
            ->orderBy('CompanyName')->paginate(10, ['*'], 'companies_page') : null;

        return view('livewire.admin.users.index', [
            'users' => $query->with(['roles', 'company', 'department'])->orderByDesc('id')->paginate(15), 'stats' => $stats, 'companies' => $companies,
            'roles' => Role::where('guard_name', 'web')->whereIn('name', $super ? ['admin', 'client', 'super-admin'] : ['client'])->orderBy('name')->get(),
            'departments' => $super ? Department::where('is_active', true)->orderBy('name')->get() : collect(),
        ])->layout('layouts.dashboard', ['title' => 'Users', 'section' => 'users']);
    }
}
