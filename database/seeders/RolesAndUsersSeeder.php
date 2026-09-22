<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'view admin dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'view reports',
        ])->map(fn (string $permission) => Permission::firstOrCreate(['name' => $permission]));

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $clientRole = Role::firstOrCreate(['name' => 'client']);

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(['view admin dashboard']);
        $clientRole->syncPermissions([]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $client = User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->syncRoles([$superAdminRole]);
        $admin->syncRoles([$adminRole]);
        $client->syncRoles([$clientRole]);
    }
}
