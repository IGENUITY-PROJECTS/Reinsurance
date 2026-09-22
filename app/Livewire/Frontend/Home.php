<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.frontend.home')
            ->layout('layouts.frontend', ['title' => config('app.name', 'Noble Portal')]);
    }
}
