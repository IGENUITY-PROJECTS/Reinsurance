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
            [$this->report->brandTitle()], [$this->report->title().' - '.$this->report->sectionLabel()],
            ['Cedant', $this->report->scopeLabel()], ['Period', $this->report->period()],
            ['Currency filter', $this->report->filters['currency'] ?? 'All currencies'],
            ['Search', $this->report->filters['q'] ?? 'None'], ['Generated', now()->format('d M Y H:i:s T')],
            [], [],
            ['Currency', 'Documents', 'Amount', 'Settled', 'Outstanding', 'Outstanding Functional Amount'],
        ];
        foreach ($this->report->totals() as $total) {
            $rows[] = [$total->CurrencyCode ?: 'Unspecified', (int) $total->document_count,
                (float) $total->amount, (float) $total->settled, (float) $total->pending, $total->outstanding_functional !== null ? (float) $total->outstanding_functional : null];
        }

        if ($this->report->isAgeing()) {
            return $rows;
        }
        $rows[] = [];
        $rows[] = ['Balance brought forward', $this->report->broughtForwardYear() ?? 'Select a period'];
        $rows[] = ['Company code', 'Currency', 'Pending amount'];
        foreach ($this->report->broughtForward() as $balance) {
            $rows[] = [$balance->CedantCode, $balance->CurrencyCode ?: 'Unspecified', (float) $balance->pending];
        }

        $rows[] = [];
        $rows[] = ['Outstanding Balance Summary'];
        $rows[] = ['Company Code', 'Currency', 'Balance Brought Forward', 'Current-Year Outstanding', 'Total Outstanding'];
        foreach ($this->report->balanceSummary() as $balance) {
            $rows[] = [$balance->company, $balance->currency, (float) $balance->opening, (float) $balance->year_pending, (float) $balance->total_pending];
        }

        $rows[] = ['Outstanding Functional Amount = Pending Amount × Exchange Rate.'];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(58);
        $sheet->getColumnDimension('B')->setWidth(45);
        foreach (['C', 'D', 'E', 'F'] as $column) {
            $sheet->getColumnDimension($column)->setWidth(22);
        }
        $sheet->getStyle('C11:F'.$sheet->getHighestRow())->getNumberFormat()->setFormatCode('#,##0.00;[Red](#,##0.00)');
        $sheet->freezePane('C11');

        return [1 => ['font' => ['bold' => true, 'size' => 16]], 2 => ['font' => ['bold' => true]], 10 => ['font' => ['bold' => true]]];
    }
}
