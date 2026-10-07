<?php

namespace App\Livewire\Admin\Users;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('manage users');
    }

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $companyCode = '';

    public array $selectedRoles = [];

    public ?int $editingUserId = null;

    public function save(): void
    {
        Gate::authorize('manage users');

        if ($this->editingUserId) {
            $this->assertCanManage(User::findOrFail($this->editingUserId));
        }
        $isSuperAdmin = auth()->user()->hasRole('super-admin');
        $isCedant = in_array('client', $this->selectedRoles, true);
        $this->companyCode = trim($this->companyCode);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->editingUserId)],
            'selectedRoles' => ['required', 'array', 'min:1'],
            'selectedRoles.*' => ['string', Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
            'companyCode' => [Rule::excludeIf(! $isCedant), 'required', 'string', 'max:50',
                Rule::exists('companies', 'CmpCode')->where('CmpSubTypeCode', '100')],
        ];

        if (! $isSuperAdmin) {
            $rules['selectedRoles.*'][] = Rule::in(['admin', 'client']);
        }

        if ($this->editingUserId) {
            $rules['password'] = ['nullable', 'string', Password::default()];
        } else {
            $rules['password'] = ['required', 'string', Password::default()];
        }

        $this->validate($rules, [
            'companyCode.required' => 'Enter the cedant company code.',
            'companyCode.exists' => 'This code is not registered as a cedant company.',
        ]);

        DB::transaction(function () use ($isCedant) {
            $company = $isCedant ? Company::cedants()->where('CmpCode', $this->companyCode)->first() : null;
            if ($isCedant && ! $company) {
                throw ValidationException::withMessages(['companyCode' => 'This cedant company is no longer available.']);
            }
            if ($this->editingUserId) {
                $user = User::query()->lockForUpdate()->findOrFail($this->editingUserId);
                $this->assertCanManage($user);
                $user->fill([
                    'name' => $this->name,
                    'email' => $this->email,
                    ...($this->password ? ['password' => Hash::make($this->password)] : []),
                ]);
            } else {
                $user = new User([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);
                $user->email_verified_at = now();
            }
            $user->company()->associate($company);
            $user->save();
            $user->syncRoles($this->selectedRoles);
        });

        $this->resetForm();
        session()->flash('status', 'User saved successfully.');
    }

    public function edit(int $userId): void
    {
        Gate::authorize('manage users');

        $user = User::findOrFail($userId);
        $this->assertCanManage($user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles()->pluck('name')->all();
        $this->companyCode = $user->company?->CmpCode ?? '';
    }

    public function delete(int $userId): void
    {
        Gate::authorize('manage users');

        abort_if(auth()->id() === $userId, 403, 'You cannot delete your own account.');

        DB::transaction(function () use ($userId) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $this->assertCanManage($user);
            $user->delete();
        });

        if ($this->editingUserId === $userId) {
            $this->resetForm();
        }

        $this->resetPage();
        session()->flash('status', 'User deleted successfully.');
    }

    private function assertCanManage(User $user): void
    {
        abort_unless(auth()->user()->hasRole('super-admin') || ! $user->hasRole('super-admin'), 403, 'Only a super-admin can manage super-admin accounts.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'selectedRoles', 'editingUserId', 'companyCode']);
        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('manage users');

        return view('livewire.admin.users.index', [
            'users' => User::query()
                ->when(! auth()->user()->hasRole('super-admin'), fn ($q) => $q->whereDoesntHave('roles', fn ($role) => $role->where('name', 'super-admin')))
                ->with(['roles', 'company'])->orderByDesc('id')->paginate(15),
            'roles' => Role::query()->where('guard_name', 'web')
                ->when(! auth()->user()->hasRole('super-admin'), fn ($q) => $q->whereIn('name', ['admin', 'client']))
                ->orderBy('name')->get(),
        ])->layout('layouts.dashboard', [
            'title' => 'User Management',
            'section' => 'users',
        ]);
    }
}
