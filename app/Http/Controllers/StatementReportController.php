<?php

namespace App\Http\Controllers;

use App\Exports\StatementWorkbook;
use App\Reports\StatementReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StatementReportController extends Controller
{
    public function index(Request $request)
    {
        $report = new StatementReport($request);

        return view('client-records.statements', [
            'report' => $report, 'records' => $report->orderedQuery()->paginate(10)->withQueryString(),
            'totals' => $report->totals(), 'currencies' => $report->currencies(), 'companies' => $report->broker ? $report->companies() : collect(),
            'broker' => $report->broker, 'prefix' => $report->broker ? 'admin' : 'client',
            'layout' => $report->broker ? 'layouts.dashboard' : 'layouts.cedant',
        ]);
    }

    public function download(Request $request, string $format)
    {
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);
        $report = new StatementReport($request, true);
        $count = $report->query()->count();
        if (! $count) {
            throw ValidationException::withMessages(['report' => 'No records found for the selected period.']);
        }
        $filename = 'cedant-'.$report->kind.'-'.$report->filters['from'].'-to-'.$report->filters['to'].'.'.$format;
        if ($format === 'xlsx') {
            return Excel::download(new StatementWorkbook($report), $filename);
        }
        if ($count > config('reports.pdf_max_records', 5000)) {
            throw ValidationException::withMessages(['report' => 'This report is too large for PDF. Select a shorter period or download Excel.']);
        }

        $pdf = Pdf::loadView('reports.statement-pdf', [
            'report' => $report, 'records' => $report->orderedQuery()->get(), 'totals' => $report->totals(),
            'generatedAt' => now(), 'logo' => $this->logo(),
        ])->setPaper('a4', 'landscape');
        $pdf->render();
        $document = $pdf->getDomPDF();
        $document->getCanvas()->page_text(730, 568, 'Page {PAGE_NUM} of {PAGE_COUNT}', $document->getFontMetrics()->getFont('DejaVu Sans', 'normal'), 7, [0.39, 0.46, 0.5]);

        return $pdf->download($filename);
    }

    private function logo(): ?string
    {
        // Embed local images only; report generation never fetches external URLs.
        $url = config('branding.logo_url');
        if (! $url || preg_match('~^[a-z]+:|^//~i', $url)) {
            return null;
        }
        $path = realpath(public_path(ltrim($url, '/')));
        $public = realpath(public_path());
        if (! $path || ! $public || ! str_starts_with(str_replace('\\', '/', $path), str_replace('\\', '/', $public).'/') || ! is_file($path) || filesize($path) > 2 * 1024 * 1024) {
            return null;
        }
        $mime = mime_content_type($path);

        return in_array($mime, ['image/png', 'image/jpeg'], true) ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path)) : null;
    }
}
