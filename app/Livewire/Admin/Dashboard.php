<?php

namespace App\Livewire\Admin;

use App\Models\ClaimSubmission;
use App\Models\Cover;
use App\Models\PremiumAdjustmentSubmission;
use App\Models\SubmissionFeedback;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $counts = [
            'claims' => ['Claims', ClaimSubmission::count()],
            'adjustments' => ['Premium adjustments', PremiumAdjustmentSubmission::count()],
            'policies' => ['Covers', Cover::count()],
            'help' => ['Help & Feedback', SubmissionFeedback::count()],
        ];
        // Portal review counts deferred.
        // $pending = ClaimSubmission::whereIn('portal_status', ['submitted', 'under_review'])->count()
        // + PremiumAdjustmentSubmission::whereIn('portal_status', ['submitted', 'under_review'])->count();
        $recent = collect();
        foreach ([ClaimSubmission::class, PremiumAdjustmentSubmission::class] as $class) {
            $recent = $recent->concat($class::with($class === ClaimSubmission::class ? ['company', 'claim'] : ['company'])->latest()->orderByDesc('id')->limit(5)->get()->map(fn ($r) => [
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
