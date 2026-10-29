<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimSubmission;
use App\Models\Company;
use App\Models\Cover;
use App\Models\CoverReinsurer;
use App\Models\PremiumAdjustment;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use App\Queries\CedantModuleRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CedantRecordsController extends Controller
{
    private function broker(): bool
    {
        return request()->routeIs('admin.*') && auth()->user()->hasAnyRole(['admin', 'super-admin']);
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
        $titles = ['all' => 'All submissions', 'claims' => 'Claims', 'adjustments' => 'Premium Adjustments'];
        abort_unless(isset($titles[$category]), 404);
        $title = $titles[$category];
        if ($category !== 'all') {
            return $this->moduleRecords($request, $category);
        }
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
        if ($this->broker()) {
            $companies = Company::query()->pluck('CmpCode')->merge($companies)->unique()->sort()->values();
        }
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
        $linkedClaims = Claim::whereIn('CedCode', $items->getCollection()->pluck('company_code'))
            ->whereIn(DB::raw('LTRIM(RTRIM(OrigClaimNo))'), $items->getCollection()->pluck('reference')->filter()->map(fn ($reference) => trim($reference)))
            ->get()->groupBy(fn ($claim) => json_encode([$claim->CedCode, trim((string) $claim->OrigClaimNo)]));
        $items->through(function ($r) use ($linkedClaims) {
            $matches = $linkedClaims->get(json_encode([$r->company_code, trim((string) $r->reference)]), collect());
            $claim = $matches->count() === 1 ? $matches->first() : null;

            return [
                'id' => $r->reference, 'route_key' => $r->submission_reference,
                'title' => $r->title ?: $r->type, 'policy' => $r->policy,
                'type' => $r->type,
                'status' => $r->type !== 'Claim' ? 'Not available'
                    : ($claim ? ($claim->MStatusDesc ?: ($claim->MStatusCode ?: 'Not available')) : 'Submitted'),
                'company' => $r->company_code,
                'date' => substr((string) $r->created_at, 0, 10),
            ];
        });
        $officialClaims = $category === 'claims'
            ? $this->owned(Claim::query(), 'CedCode', $code)->when($this->broker() && ! empty($filter['company']), fn ($q) => $q->where('CedCode', $filter['company']))->orderBy('ClaimNo')->paginate(10, ['*'], 'rbs_page')->withQueryString()
            : null;

        return $this->screen('submission-list', [
            'title' => $title, 'category' => $category, 'items' => $items,
            'statuses' => $statuses, 'companies' => $companies, 'officialClaims' => $officialClaims, 'officialAdjustments' => $category === 'adjustments' ? $this->adjustments($request)->orderByDesc('DocumentDate')->orderBy('id')->paginate(10, ['*'], 'rbs_page')->withQueryString() : null,
        ]);
    }

    private function moduleRecords(Request $request, string $category)
    {
        $code = $this->code($request);
        $filter = $request->validate([
            'company' => 'nullable|string|max:50', 'q' => 'nullable|string|max:150',
            'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from',
        ]);
        $query = app(CedantModuleRecords::class)->query($category, $this->broker() ? null : $code);
        $companies = (clone $query)->distinct()->orderBy('company_code')->pluck('company_code');
        if ($this->broker()) {
            $companies = Company::query()->pluck('CmpCode')->merge($companies)->unique()->sort()->values();
        }
        if ($this->broker() && ! empty($filter['company'])) {
            $query->where('company_code', $filter['company']);
        }
        if (! empty($filter['q'])) {
            $term = '%'.$filter['q'].'%';
            $query->where(fn ($q) => $q->where('reference', 'like', $term)->orWhere('cover', 'like', $term)->orWhere('label', 'like', $term)->orWhere('claim_number', 'like', $term));
        }
        $query->when(! empty($filter['from']), fn ($q) => $q->where('display_date', '>=', $filter['from']))
            ->when(! empty($filter['to']), fn ($q) => $q->where('display_date', '<=', $filter['to']));
        $records = $query->orderByDesc('display_date')->orderBy('record_kind')->orderBy('record_key')->paginate(10)->withQueryString();

        return $this->screen('client-records.module-records', compact('category', 'records', 'companies'));
    }

    public function show(Request $request, string $id)
    {
        $code = $this->code($request);
        $submission = $this->owned(ClaimSubmission::query(), 'company_code', $code)->where('submission_reference', $id)
            ->first();
        $isClaim = $submission !== null;
        $submission ??= $this->owned(PremiumAdjustmentSubmission::query(), 'company_code', $code)
            ->where('submission_reference', $id)->firstOrFail();
        $matches = $isClaim ? $submission->matchingClaims()->limit(2)->get() : collect();
        $official = $matches->count() === 1 ? $matches->first() : null;

        $documents = $submission->documents()->orderByDesc('id')->paginate(10, ['*'], 'documents_page')->withQueryString();
        $history = $isClaim ? $submission->statusHistories()->reorder()->with('changedBy')->orderByDesc('changed_at')->orderByDesc('id')->paginate(10, ['*'], 'history_page')->withQueryString() : null;
        $officialAdjustments = ! $isClaim ? $this->adjustments($request)->forCompany($submission->company_code)->where('CoverNo', $submission->CoverNo)->orderByDesc('DocumentDate')->orderBy('id')->paginate(10, ['*'], 'rbs_page')->withQueryString() : null;

        $reinsurers = $isClaim ? $this->claimCoverReinsurers($official?->CoverNo ?: $submission->CoverNo, $submission->company_code) : null;

        return $this->screen('client-records.submission', compact('submission', 'isClaim', 'official', 'documents', 'history', 'officialAdjustments', 'reinsurers'));
    }

    public function covers(Request $request)
    {
        $query = $this->owned(Cover::query(), 'CusCode', $this->code($request));
        $filter = $request->validate(['q' => 'nullable|string|max:150', 'company' => 'nullable|string|max:50', 'reinsurance_type' => 'nullable|in:FC,TC']);
        $companies = (clone $query)->select('CusCode')->selectRaw('MAX(CusName) as company_name')
            ->whereNotNull('CusCode')->groupBy('CusCode')->orderBy('CusCode')->get();
        if ($this->broker()) {
            $companies = Company::query()->selectRaw('CmpCode as CusCode, CompanyName as company_name')->get()->concat($companies)->unique('CusCode')->sortBy('CusCode')->values();
        }
        if (filled($filter['company'] ?? null)) {
            $query->where('CusCode', $filter['company']);
        }
        if (filled($filter['reinsurance_type'] ?? null)) {
            $query->where('RETypeCode', $filter['reinsurance_type']);
        }
        if (! empty($filter['q'])) {
            $term = '%'.$filter['q'].'%';
            $query->where(fn ($q) => $q->where('CoverNo', 'like', $term)->orWhere('MRef', 'like', $term)->orWhere('InsName', 'like', $term)->orWhere('CusCode', 'like', $term)->orWhere('CusName', 'like', $term));
        }
        $covers = $query->orderBy('CoverNo')->paginate(10)->withQueryString();

        return $this->screen('client-records.covers', compact('covers', 'companies'));
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

        $submission = ClaimSubmission::where('company_code', $claim->CedCode)
            ->whereRaw('LTRIM(RTRIM(OrigClaimNo)) = ?', [trim((string) $claim->OrigClaimNo)])
            ->when(trim((string) $claim->OrigClaimNo) === '', fn ($q) => $q->whereRaw('1 = 0'))->first();
        $documents = $submission?->documents()->orderByDesc('id')->paginate(10)->withQueryString();

        $reinsurers = $this->claimCoverReinsurers($claim->CoverNo, $claim->CedCode);

        return $this->screen('client-records.claim', compact('claim', 'submission', 'documents', 'reinsurers'));
    }

    private function claimCoverReinsurers(?string $coverNo, ?string $companyCode)
    {
        return CoverReinsurer::query()->where('CoverNo', $coverNo)->where('CedCode', $companyCode)
            ->when(blank($coverNo) || blank($companyCode), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('DetailID')->orderBy('id')->paginate(10, ['*'], 'reinsurers_page')->withQueryString();
    }

    public function officialAdjustment(Request $request, int $id)
    {
        $adjustment = $this->adjustments($request)->whereKey($id)->firstOrFail();

        return $this->screen('client-records.adjustment', compact('adjustment'));
    }

    private function adjustments(Request $request)
    {
        $query = PremiumAdjustment::query()->withInvoiceAmount();
        if (! $this->broker()) {
            $query->forCompany($this->code($request));
        } elseif ($request->filled('company')) {
            $query->forCompany($request->string('company')->toString());
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
