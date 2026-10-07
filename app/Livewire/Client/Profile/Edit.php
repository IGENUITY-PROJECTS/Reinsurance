<?php

namespace App\Livewire\Client\Profile;

use Livewire\Component;

class Edit extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.client.profile.edit', [
            'user' => $user,
            'company' => $user->company,
        ])->layout('layouts.cedant', [
            'title' => 'My account',
            'section' => 'client-profile',
        ]);
    }
}
