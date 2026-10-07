<?php

namespace App\Exports;

use App\Reports\StatementReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StatementWorkbook implements WithMultipleSheets
{
    public function __construct(private readonly StatementReport $report) {}

    public function sheets(): array
    {
        return [new StatementSummarySheet($this->report), new StatementRecordsSheet($this->report)];
    }
}
