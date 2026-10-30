<?php

namespace App\Exports;

use App\Reports\StatementReport;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StatementRecordsSheet extends StatementValueBinder implements FromQuery, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle
{
    public function __construct(private readonly StatementReport $report) {}

    public function query()
    {
        return $this->report->orderedQuery();
    }

    public function headings(): array
    {
        return $this->report->headings();
    }

    public function title(): string
    {
        return $this->report->sectionLabel() === 'Ageing' ? 'Ageing' : 'Documents';
    }

    public function map($record): array
    {
        $row = $this->report->row($record);
        if ($this->report->isAgeing()) {
            return array_map(fn ($value, $i) => $i >= 3 && $value !== null ? (float) $value : $value, $row, array_keys($row));
        }
        $row[array_search('Age Days', $this->headings(), true)] = (int) $record->age_days;
        $row[1] = $record->DocumentDate ? Date::dateTimeToExcel($record->DocumentDate) : null;
        if ($this->report->isClaims()) {
            $row[array_search('Date Of Loss', $this->headings(), true)] = $record->DOL ? Date::dateTimeToExcel($record->DOL) : null;
        }

        for ($i = $this->report->amountOffset(); $i < count($row); $i++) {
            $row[$i] = $row[$i] === null ? null : (float) $row[$i];
        }

        return $row;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
            $lastRow = max(2, $sheet->getHighestRow());
            $sheet->freezePane('C2');
            $sheet->setAutoFilter('A1:'.$lastColumn.$lastRow);
            $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:'.$lastColumn.'1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF175B56');
            $sheet->getRowDimension(1)->setRowHeight(28);
            foreach ($this->headings() as $i => $heading) {
                $column = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->getColumnDimension($column)->setWidth(in_array($heading, ['Cedant', 'Insured', 'Risk Reference', 'Reinsurer', 'Account Type', 'Risk Details']) ? 34 : 20);
                if (($this->report->isAgeing() && $i >= 3) || in_array($heading, ['Amount', 'Settled', 'Pending Amount', 'Functional Amount', 'Outstanding Functional Amount', 'Running Balance'])) {
                    $sheet->getStyle($column.'2:'.$column.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00;[Red](#,##0.00)');
                }
                if (in_array($heading, ['Account Type', 'Risk Details'], true)) {
                    $sheet->getStyle($column.'2:'.$column.$lastRow)->getAlignment()->setWrapText(true);
                }
                if ($heading === 'Exchange Rate') {
                    $sheet->getStyle($column.'2:'.$column.$lastRow)->getNumberFormat()->setFormatCode('0.00000000');
                }
                if (in_array($heading, ['Date', 'Date Of Loss'])) {
                    $sheet->getStyle($column.'2:'.$column.$lastRow)->getNumberFormat()->setFormatCode('dd mmm yyyy');
                }
            }
        }];
    }
}
