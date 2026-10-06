<?php

namespace App\Http\Controllers;

use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimSubmission;
use App\Models\Cover;
use App\Models\PremiumAdjustment;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use App\Models\SubmissionFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CedantRecordsController extends Controller
{
    private function broker(): bool
    {
        return request()->routeIs('admin.*') && (auth()->user()->hasAnyRole(['admin', 'super-admin']) || auth()->user()->can('view admin dashboard'));
    }

    private function screen(string $view, array $data = [])
    {
        return view($view, array_merge($data, [
            'broker' => $this->broker(), 'prefix' => $this->broker() ? 'admin' : 'client',
            'layout' => $this->broker() ? 'layouts.dashboard' : 'layouts.cedant',
        ]));
    }

    private function code(Request $request): string
    {
        // Blank/unassigned accounts never fall back to another company's data.
        return $request->user()->company?->CmpCode ?? '';
    }

    private function owned($query, string $field, string $code)
    {
        if ($this->broker()) {
            return $query;
        }

        return $query->where($field, $code)->when($code === '', fn ($q) => $q->whereRaw('1 = 0'));
    }

    public function index(Request $request)
    {
        $category = $request->route()->defaults['category'] ?? 'all';
        $code = $this->code($request);
        if ($category === 'help') {
            $messages = SubmissionFeedback::with(['author', 'claimSubmission.company', 'premiumAdjustmentSubmission.company'])
                ->where(function ($query) use ($code) {
                    $query->whereHas('claimSubmission', fn ($submission) => $this->owned($submission, 'company_code', $code))
                        ->orWhereHas('premiumAdjustmentSubmission', fn ($submission) => $this->owned($submission, 'company_code', $code));
                })->latest()->orderByDesc('id')->paginate(15)->withQueryString();

            return $this->screen('client-records.feedback', compact('messages'));
        }
        $titles = ['all' => 'All submissions', 'claims' => 'Claims', 'adjustments' => 'Premium Adjustments'];
        abort_unless(isset($titles[$category]), 404);
        $title = $titles[$category];
        $filter = $request->validate(['company' => 'nullable|string|max:50', 'q' => 'nullable|string|max:150', 'status' => 'nullable|string|max:80', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $claims = $this->owned(DB::table('claim_submissions'), 'company_code', $code)
            ->select(['submission_reference', 'OrigClaimNo as reference', 'InsuredName as title', 'CoverNo as policy', 'portal_status as status', 'created_at', 'company_code'])
            ->addSelect('ClaimNo as claim_number')
            ->selectRaw("'Claim' as type");
        $adjustments = $this->owned(DB::table('premium_adjustment_submissions'), 'company_code', $code)
            ->select(['submission_reference', 'CoverNo as reference', 'details as title', 'CoverNo as policy', 'portal_status as status', 'created_at', 'company_code'])
            ->selectRaw("NULL as claim_number, 'Premium adjustment' as type");
        $base = match ($category) {
            'claims' => $claims, 'adjustments' => $adjustments,
            default => $claims->unionAll($adjustments),
        };
        $query = DB::query()->fromSub($base, 'submissions');
        $companies = (clone $query)->distinct()->orderBy('company_code')->pluck('company_code');
        if ($this->broker() && ! empty($filter['company'])) {
            $query->where('company_code', $filter['company']);
        }

        $statuses = (clone $query)->distinct()->pluck('status');
        if (! empty($filter['q'])) {
            $term = '%'.$filter['q'].'%';
            $query->where(fn ($q) => $q->where('reference', 'like', $term)->orWhere('title', 'like', $term)->orWhere('policy', 'like', $term));
        }
        // Portal status filtering deferred.
        // if (! empty($filter['status'])) {
        // $query->where('status', $filter['status']);
        // }
        if (! empty($filter['from'])) {
            $query->whereDate('created_at', '>=', $filter['from']);
        }
        if (! empty($filter['to'])) {
            $query->whereDate('created_at', '<=', $filter['to']);
        }
        $items = $query->orderByDesc('created_at')->orderBy('submission_reference')->paginate(10)->withQueryString();
        $linkedClaims = Claim::whereIn('ClaimNo', $items->getCollection()->pluck('claim_number')->filter())
            ->get()->keyBy('ClaimNo');
        $items->through(fn ($r) => [
            'id' => $r->reference, 'route_key' => $r->submission_reference,
            'title' => $r->title ?: $r->type, 'policy' => $r->policy,
            'type' => $r->type,
            'status' => $r->type !== 'Claim' ? 'Not available'
                : (($linked = $linkedClaims->get($r->claim_number))
                    && $linked->CedCode === $r->company_code
                    && (! $r->policy || $linked->CoverNo === $r->policy)
                    ? ($linked->MStatusDesc ?: ($linked->MStatusCode ?: 'Not available'))
                    : 'Awaiting RBS update'),
            'company' => $r->company_code,
            'date' => substr((string) $r->created_at, 0, 10),
        ]);
        $officialClaims = $category === 'claims'
            ? $this->owned(Claim::query(), 'CedCode', $code)->when($this->broker() && ! empty($filter['company']), fn ($q) => $q->where('CedCode', $filter['company']))->orderBy('ClaimNo')->paginate(10, ['*'], 'rbs_page')->withQueryString()
            : null;

        return $this->screen('submission-list', [
            'title' => $title, 'category' => $category, 'items' => $items,
            'statuses' => $statuses, 'companies' => $companies, 'officialClaims' => $officialClaims, 'officialAdjustments' => $category === 'adjustments' ? $this->adjustments($request)->orderByDesc('DocumentDate')->orderBy('id')->paginate(10, ['*'], 'rbs_page')->withQueryString() : null,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $code = $this->code($request);
        $submission = $this->owned(ClaimSubmission::query(), 'company_code', $code)->where('submission_reference', $id)
            ->first();
        $isClaim = $submission !== null;
        $submission ??= $this->owned(PremiumAdjustmentSubmission::query(), 'company_code', $code)
            ->where('submission_reference', $id)->firstOrFail();
        $official = $isClaim && $submission->ClaimNo
            ? Claim::where('CedCode', $submission->company_code)->where('ClaimNo', $submission->ClaimNo)
                ->when($submission->CoverNo, fn ($q) => $q->where('CoverNo', $submission->CoverNo))->first()
            : null;

        $documents = $submission->documents()->orderByDesc('id')->paginate(10, ['*'], 'documents_page')->withQueryString();
        $feedback = $submission->feedback()->reorder()->with('author')->latest()->orderByDesc('id')->paginate(10, ['*'], 'feedback_page')->withQueryString();
        $history = $isClaim ? $submission->statusHistories()->reorder()->with('changedBy')->orderByDesc('changed_at')->orderByDesc('id')->paginate(10, ['*'], 'history_page')->withQueryString() : null;
        $officialAdjustments = ! $isClaim ? $this->adjustments($request)->where('CoverNo', $submission->CoverNo)->whereHas('cover', fn ($q) => $q->where('CusCode', $submission->company_code))->orderByDesc('DocumentDate')->orderBy('id')->paginate(10, ['*'], 'rbs_page')->withQueryString() : null;

        return $this->screen('client-records.submission', compact('submission', 'isClaim', 'official', 'documents', 'feedback', 'history', 'officialAdjustments'));
    }

    public function covers(Request $request)
    {
        $query = $this->owned(Cover::query(), 'CusCode', $this->code($request));
        $filter = $request->validate(['q' => 'nullable|string|max:150']);
        if (! empty($filter['q'])) {
            $term = '%'.$filter['q'].'%';
            $query->where(fn ($q) => $q->where('CoverNo', 'like', $term)->orWhere('MRef', 'like', $term)->orWhere('InsName', 'like', $term));
        }
        $covers = $query->orderBy('CoverNo')->paginate(10)->withQueryString();

        return $this->screen('client-records.covers', compact('covers'));
    }

    public function cover(Request $request, string $number)
    {
        $cover = $this->owned(Cover::query(), 'CusCode', $this->code($request))
            ->where('CoverNo', $number)->firstOrFail();
        $reinsurers = $cover->reinsurers()->where('CedCode', $cover->CusCode)->orderBy('DetailID')->paginate(10)->withQueryString();

        return $this->screen('client-records.cover', compact('cover', 'reinsurers'));
    }

    public function officialClaim(Request $request, string $number)
    {
        $claim = $this->owned(Claim::query(), 'CedCode', $this->code($request))->where('ClaimNo', $number)->firstOrFail();

        return $this->screen('client-records.claim', compact('claim'));
    }

    public function statements(Request $request)
    {
        $filter = $request->validate(['kind' => 'nullable|in:premiums,claims', 'q' => 'nullable|string|max:150', 'currency' => 'nullable|string|max:50']);
        $kind = $filter['kind'] ?? 'premiums';
        $query = $this->owned($kind === 'premiums' ? CedantPremium::query() : CedantClaimPayable::query(), 'CedantCode', $this->code($request));
        $currencies = (clone $query)->whereNotNull('CurrencyCode')->distinct()->pluck('CurrencyCode');
        if (! empty($filter['currency'])) {
            $query->where('CurrencyCode', $filter['currency']);
        }
        if (! empty($filter['q'])) {
            $term = '%'.$filter['q'].'%';
            $query->where(fn ($q) => $q->where('DocumentNo', 'like', $term)->orWhere('RiskNoteRef', 'like', $term));
        }
        $records = $query->orderByDesc('DocumentDate')->orderBy('id')->paginate(10)->withQueryString();

        return $this->screen('client-records.statements', compact('records', 'kind', 'currencies'));
    }

    public function officialAdjustment(Request $request, int $id)
    {
        $adjustment = $this->adjustments($request)->whereKey($id)->firstOrFail();

        return $this->screen('client-records.adjustment', compact('adjustment'));
    }

    private function adjustments(Request $request)
    {
        $query = PremiumAdjustment::query();
        if (! $this->broker()) {
            $query->whereHas('cover', fn ($q) => $this->owned($q, 'CusCode', $this->code($request)));
        } elseif ($request->filled('company')) {
            $query->whereHas('cover', fn ($q) => $q->where('CusCode', $request->string('company')->toString()));
        }

        return $query;
    }

    public function document(Request $request, string $kind, int $id)
    {
        abort_unless(in_array($kind, ['claims', 'adjustments'], true), 404);
        $model = $kind === 'claims' ? ClaimDocument::class : PremiumAdjustmentDocument::class;
        $document = $model::whereKey($id)->whereHas('submission', fn ($q) => $this->owned($q, 'company_code', $this->code($request)))->firstOrFail();
        abort_unless($document->disk === 'local' && ! str_contains($document->path, '..') && Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, basename($document->original_name));
    }
}
