<?php

namespace App\Livewire\Admin\Access;

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
        $firstRole = Role::query()->orderBy('name')->first();

        if ($firstRole) {
            $this->selectRole($firstRole->id);
        }
    }

    public function createRole(): void
    {
        $this->roleName = str($this->roleName)->lower()->trim()->replace(' ', '-')->toString();

        $this->validate([
            'roleName' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        Role::create(['name' => $this->roleName]);

        $this->reset('roleName');
        session()->flash('status', 'Role created successfully.');
    }

    public function createPermission(): void
    {
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
        $role = Role::findOrFail($roleId);

        $this->selectedRoleId = $role->id;
        $this->selectedPermissions = $role->permissions()->pluck('name')->all();
    }

    public function saveRolePermissions(): void
    {
        $this->validate([
            'selectedRoleId' => ['required', 'exists:roles,id'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ]);

        Role::findOrFail($this->selectedRoleId)->syncPermissions($this->selectedPermissions);

        session()->flash('status', 'Role permissions updated successfully.');
    }

    public function selectAllPermissions(): void
    {
        $this->selectedPermissions = Permission::query()->pluck('name')->all();
    }

    public function clearPermissions(): void
    {
        $this->selectedPermissions = [];
    }

    public function render()
    {
        $selectedRole = $this->selectedRoleId
            ? Role::query()->with('permissions')->find($this->selectedRoleId)
            : null;

        $permissions = Permission::query()->orderBy('name')->get();

        return view('livewire.admin.access.index', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => $permissions,
            'permissionGroups' => $permissions->groupBy(fn (Permission $permission) => str($permission->name)->before(' ')->headline()->toString()),
            'selectedRole' => $selectedRole,
        ])->layout('layouts.dashboard', [
            'title' => 'Roles & Permissions',
            'section' => 'access',
        ]);
    }
}
