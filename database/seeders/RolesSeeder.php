<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect([
            'view admin dashboard', 'manage users', 'manage roles',
            'manage permissions', 'view reports',
        ])->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $super = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        // Add defaults without removing permissions granted by the administrator.
        $super->givePermissionTo($permissions);
        $admin->givePermissionTo('view admin dashboard');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
