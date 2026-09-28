<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SubmissionPreviewController extends Controller
{
    private function context(Request $request): array
    {
        $broker = $request->routeIs('admin.*');
        return [$broker, $broker ? 'admin' : 'client', $broker ? 'layouts.dashboard' : 'layouts.cedant'];
    }

    private function records(bool $broker)
    {
        $records = collect(config('portal_demo.submissions'))->concat(collect(config('portal_demo.feedback'))->map(fn ($r) => [
            'id' => $r['id'], 'company' => $r['company'], 'type' => 'Help & Feedback', 'title' => $r['subject'],
            'date' => '2026-09-23', 'status' => $r['status'], 'policy' => 'Not applicable', 'documents' => [],
            'history' => array_values(array_filter([
                ['2026-09-23', 'Submitted', 'Cedant', $r['message']],
                $r['reply'] ? ['2026-09-23', 'Answered', 'Broker', $r['reply']] : null,
            ])),
        ]));
        // Fictional preview company only; replace with authenticated company scope when tables are implemented.
        return $broker ? $records : $records->where('company', config('portal_demo.cedant'));
    }

    public function index(Request $request)
    {
        [$broker, $prefix, $layout] = $this->context($request);
        $category = $request->route('category') ?? $request->route()->defaults['category'] ?? 'all';
        $types = ['all' => null, 'claims' => 'Claim', 'adjustments' => 'Premium adjustment', 'commissions' => 'Profit commission', 'help' => 'Help & Feedback'];
        abort_unless(array_key_exists($category, $types), 404);
        $title = ['all' => 'All submissions', 'claims' => 'Claims', 'adjustments' => 'Premium Adjustments', 'commissions' => 'Profit Commissions', 'help' => 'Help & Feedback'][$category];
        $base = $this->records($broker)->when($types[$category], fn ($r) => $r->where('type', $types[$category]));
        $statuses = $base->pluck('status')->unique()->sort();
        $companies = $base->pluck('company')->unique()->sort();
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'status' => 'nullable|string|max:80', 'company' => 'nullable|string|max:150', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'page' => 'nullable|integer|min:1']);
        $rows = $base->filter(function ($r) use ($filters, $broker) {
            return (empty($filters['q']) || str_contains(mb_strtolower($r['id'].' '.$r['title'].' '.$r['company'].' '.$r['policy']), mb_strtolower($filters['q'])))
                && (empty($filters['status']) || $r['status'] === $filters['status'])
                && (!$broker || empty($filters['company']) || $r['company'] === $filters['company'])
                && (empty($filters['from']) || $r['date'] >= $filters['from'])
                && (empty($filters['to']) || $r['date'] <= $filters['to']);
        })->sortByDesc('date')->values();
        $page = (int) ($filters['page'] ?? 1);
        $items = new LengthAwarePaginator($rows->forPage($page, 5)->values(), $rows->count(), 5, $page, ['path' => $request->url(), 'query' => $request->query()]);
        return view('submission-list', compact('broker', 'prefix', 'layout', 'title', 'items', 'statuses', 'companies', 'category'));
    }

    public function show(Request $request, string $id)
    {
        [$broker, $prefix, $layout] = $this->context($request);
        $item = $this->records($broker)->firstWhere('id', $id);
        abort_unless($item, 404);
        return view('submission-detail', compact('broker', 'prefix', 'layout', 'item'));
    }
}
