<?php

namespace App\Livewire\Admin\Access;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public string $roleName = '';

    public string $permissionName = '';

    public ?int $selectedRoleId = null;

    public array $selectedPermissions = [];

    public function mount(): void
    {
        Gate::authorize('manage roles');

        $firstRole = Role::query()->when(! auth()->user()->hasRole('super-admin'), fn ($q) => $q->where('name', '!=', 'super-admin'))->orderBy('name')->first();

        if ($firstRole) {
            $this->selectRole($firstRole->id);
        }
    }

    public function createRole(): void
    {
        Gate::authorize('manage roles');

        $this->roleName = str($this->roleName)->lower()->trim()->replace(' ', '-')->toString();
        abort_if($this->roleName === 'super-admin' && ! auth()->user()->hasRole('super-admin'), 403);

        $this->validate([
            'roleName' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        Role::create(['name' => $this->roleName]);

        $this->reset('roleName');
        session()->flash('status', 'Role created successfully.');
    }

    public function createPermission(): void
    {
        Gate::authorize('manage roles');

        $this->permissionName = str($this->permissionName)->lower()->trim()->squish()->toString();

        $this->validate([
            'permissionName' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $this->permissionName]);

        $this->reset('permissionName');
        session()->flash('status', 'Permission created successfully.');
    }

    public function selectRole(int $roleId): void
    {
        Gate::authorize('manage roles');

        $role = Role::findOrFail($roleId);
        $this->assertCanManageRole($role);

        $this->selectedRoleId = $role->id;
        $this->selectedPermissions = $role->permissions()->pluck('name')->all();
    }

    public function saveRolePermissions(): void
    {
        Gate::authorize('manage roles');

        $this->validate([
            'selectedRoleId' => ['required', 'exists:roles,id'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::findOrFail($this->selectedRoleId);
        $this->assertCanManageRole($role);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('status', 'Role permissions updated successfully.');
    }

    public function selectAllPermissions(): void
    {
        Gate::authorize('manage roles');

        $this->selectedPermissions = Permission::query()->pluck('name')->all();
    }

    public function clearPermissions(): void
    {
        Gate::authorize('manage roles');

        $this->selectedPermissions = [];
    }

    private function assertCanManageRole(Role $role): void
    {
        abort_unless(auth()->user()->hasRole('super-admin') || $role->name !== 'super-admin', 403, 'Only a super-admin can manage the super-admin role.');
    }

    public function render()
    {
        Gate::authorize('manage roles');

        $selectedRole = $this->selectedRoleId
            ? Role::query()->with('permissions')->find($this->selectedRoleId)
            : null;

        if ($selectedRole) {
            $this->assertCanManageRole($selectedRole);
        }
        $permissions = Permission::query()->orderBy('name')->get();

        return view('livewire.admin.access.index', [
            'roles' => Role::query()->with('permissions')
                ->when(! auth()->user()->hasRole('super-admin'), fn ($q) => $q->where('name', '!=', 'super-admin'))
                ->orderBy('name')->get(),
            'permissions' => $permissions,
            'permissionGroups' => $permissions->groupBy(fn (Permission $permission) => str($permission->name)->before(' ')->headline()->toString()),
            'selectedRole' => $selectedRole,
        ])->layout('layouts.dashboard', [
            'title' => 'Roles & Permissions',
            'section' => 'access',
        ]);
    }
}
