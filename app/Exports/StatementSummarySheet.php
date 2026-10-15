<?php

namespace App\Exports;

use App\Reports\StatementReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatementSummarySheet extends StatementValueBinder implements FromArray, WithCustomValueBinder, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(private readonly StatementReport $report) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $rows = [
            [config('branding.company_name')], [$this->report->title()],
            ['Cedant', $this->report->scopeLabel()], ['Period', $this->report->period()],
            ['Currency filter', $this->report->filters['currency'] ?? 'All currencies'],
            ['Search', $this->report->filters['q'] ?? 'None'], ['Generated', now()->format('Y-m-d H:i:s T')],
            ['Balances as currently recorded. Fully settled documents excluded.'], [],
            ['Currency', 'Documents', 'Amount', 'Settled', 'Outstanding'],
        ];
        foreach ($this->report->totals() as $total) {
            $rows[] = [$total->CurrencyCode ?: 'Unspecified', (int) $total->document_count,
                (float) $total->amount, (float) $total->settled, (float) $total->pending];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(58);
        $sheet->getColumnDimension('B')->setWidth(45);
        foreach (['C', 'D', 'E'] as $column) {
            $sheet->getColumnDimension($column)->setWidth(22);
        }
        $sheet->getStyle('C11:E'.$sheet->getHighestRow())->getNumberFormat()->setFormatCode('#,##0.00;[Red](#,##0.00)');
        $sheet->freezePane('C11');

        return [1 => ['font' => ['bold' => true, 'size' => 16]], 2 => ['font' => ['bold' => true]], 10 => ['font' => ['bold' => true]]];
    }
}
