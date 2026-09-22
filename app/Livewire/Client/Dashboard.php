<?php

namespace App\Livewire\Client;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.client.dashboard')
            ->layout('layouts.cedant', [
                'title' => 'Home',
                'section' => 'client',
            ]);
    }
}

