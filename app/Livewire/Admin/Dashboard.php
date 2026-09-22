<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'permissionCount' => Permission::count(),
        ])
            ->layout('layouts.dashboard', [
                'title' => 'Control Center',
                'section' => 'admin',
            ]);
    }
}
