<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public array $selectedRoles = [];

    public ?int $editingUserId = null;

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->editingUserId)],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', Rule::exists(Role::class, 'name')],
        ];

        if ($this->editingUserId) {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                ...($this->password ? ['password' => Hash::make($this->password)] : []),
            ]);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);
        }

        $user->syncRoles($this->selectedRoles);

        $this->resetForm();
        session()->flash('status', 'User saved successfully.');
    }

    public function edit(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles()->pluck('name')->all();
    }

    public function delete(int $userId): void
    {
        abort_if(auth()->id() === $userId, 403, 'You cannot delete your own account.');

        User::findOrFail($userId)->delete();

        if ($this->editingUserId === $userId) {
            $this->resetForm();
        }

        session()->flash('status', 'User deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'selectedRoles', 'editingUserId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.users.index', [
            'users' => User::query()->with('roles')->latest()->get(),
            'roles' => Role::query()->orderBy('name')->get(),
        ])->layout('layouts.dashboard', [
            'title' => 'User Management',
            'section' => 'users',
        ]);
    }
}
