<?php

namespace App\Livewire\Admin;

use App\Models\ClaimSubmission;
use App\Models\Cover;
use App\Models\FeedbackConversation;
use App\Models\PremiumAdjustmentSubmission;
use App\Queries\CedantModuleRecords;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $counts = [
            'claims' => ['Claims', app(CedantModuleRecords::class)->query('claims')->count()],
            'adjustments' => ['Premium adjustments', app(CedantModuleRecords::class)->query('adjustments')->count()],
            'policies' => ['Covers', Cover::count()],
            'help' => ['Help & Feedback', FeedbackConversation::query()->when(! auth()->user()->hasRole('super-admin'), fn ($q) => $q->where('department_id', auth()->user()->department_id)->when(! auth()->user()->department_id, fn ($q) => $q->whereRaw('1 = 0'))->whereHas('department', fn ($q) => $q->where('is_active', true)))->count()],
        ];
        // Portal review counts deferred.
        // $pending = ClaimSubmission::whereIn('portal_status', ['submitted', 'under_review'])->count()
        // + PremiumAdjustmentSubmission::whereIn('portal_status', ['submitted', 'under_review'])->count();
        $recent = collect();
        foreach ([ClaimSubmission::class, PremiumAdjustmentSubmission::class] as $class) {
            $recent = $recent->concat($class::with('company')->latest()->orderByDesc('id')->limit(5)->get()->map(fn ($r) => [
                'id' => $r instanceof ClaimSubmission ? $r->OrigClaimNo : $r->CoverNo,
                'route_key' => $r->submission_reference, 'company' => $r->company?->CompanyName ?? $r->company_code,
                'title' => $r instanceof ClaimSubmission ? ($r->InsuredName ?: 'Claim') : 'Premium adjustment',
                'type' => $r instanceof ClaimSubmission ? 'Claim' : 'Premium adjustment',
                'status' => $r instanceof ClaimSubmission ? $r->rbs_status : 'Not available', 'date' => $r->created_at->format('Y-m-d'), 'sort' => $r->created_at,
            ]));
        }
        $recent = $recent->sortByDesc('sort')->take(5);

        return view('livewire.admin.dashboard', compact('counts', 'recent'))
            ->layout('layouts.dashboard', ['title' => 'Broker overview', 'section' => 'admin']);
    }
}
