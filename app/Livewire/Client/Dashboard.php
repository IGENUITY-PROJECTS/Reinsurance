<?php

namespace App\Livewire\Client;

use App\Models\ClaimSubmission;
use App\Models\Cover;
use App\Models\PremiumAdjustmentSubmission;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $company = auth()->user()->company;
        $code = $company?->CmpCode ?? '';
        $claims = ClaimSubmission::where('company_code', $code)->when($code === '', fn ($q) => $q->whereRaw('1=0'));
        $adjustments = PremiumAdjustmentSubmission::where('company_code', $code)->when($code === '', fn ($q) => $q->whereRaw('1=0'));
        $counts = [
            'Covers' => Cover::where('CusCode', $code)->when($code === '', fn ($q) => $q->whereRaw('1=0'))->count(),
            'Claim submissions' => (clone $claims)->count(),
            'Premium adjustments' => (clone $adjustments)->count(),
        ];
        $recent = (clone $claims)->latest()->orderByDesc('id')->take(3)->get()->map(fn ($r) => [
            'id' => $r->OrigClaimNo, 'route_key' => $r->submission_reference, 'title' => $r->InsuredName ?: 'Claim',
            'type' => 'Claim', 'status' => $r->portal_status, 'date' => $r->created_at->format('Y-m-d'),
        ])->concat((clone $adjustments)->latest()->orderByDesc('id')->take(3)->get()->map(fn ($r) => [
            'id' => $r->CoverNo, 'route_key' => $r->submission_reference, 'title' => $r->details ?: 'Premium adjustment',
            'type' => 'Premium adjustment', 'status' => $r->portal_status, 'date' => $r->created_at->format('Y-m-d'),
        ]))->sortByDesc('date')->take(3);

        return view('livewire.client.dashboard', compact('company', 'counts', 'recent'))
            ->layout('layouts.cedant', ['title' => 'Home', 'section' => 'client']);
    }
}
