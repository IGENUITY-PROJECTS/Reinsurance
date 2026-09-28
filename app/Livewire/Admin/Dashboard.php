<?php
namespace App\Livewire\Admin;
use Livewire\Component;
class Dashboard extends Component
{
    public function render()
    {
        $all = collect(config('portal_demo.submissions'));
        return view('livewire.admin.dashboard', ['all' => $all, 'recent' => $all->sortByDesc('date')->take(3)])
            ->layout('layouts.dashboard', ['title' => 'Broker overview', 'section' => 'admin']);
    }
}
