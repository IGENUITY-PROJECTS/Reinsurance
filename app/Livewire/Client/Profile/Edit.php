<?php

namespace App\Livewire\Client\Profile;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function save(): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            ...($this->password ? ['password' => Hash::make($this->password)] : []),
        ]);

        $this->reset(['password', 'password_confirmation']);
        session()->flash('status', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.client.profile.edit')
            ->layout('layouts.cedant', [
                'title' => 'My account',
                'section' => 'client-profile',
            ]);
    }
}

