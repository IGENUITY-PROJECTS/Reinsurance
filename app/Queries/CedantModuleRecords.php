<?php

namespace App\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CedantModuleRecords
{
    public function query(string $category, ?string $companyCode = null): Builder
    {
        [$submitted, $mirrored] = $category === 'claims' ? $this->claims() : $this->adjustments();
        $query = DB::query()->fromSub($submitted->unionAll($mirrored), 'records');
        if ($companyCode !== null) {
            $query->where('company_code', $companyCode)->when($companyCode === '', fn ($q) => $q->whereRaw('1 = 0'));
        }

        return $query;
    }

    private function claims(): array
    {
        // Each official claim stays visible, even when several share a reference.
        $mirrored = DB::table('claims as c')->leftJoin('claim_submissions as s', function ($join) {
            $join->on('s.company_code', '=', 'c.CedCode')
                ->whereRaw("LTRIM(RTRIM(c.OrigClaimNo)) = LTRIM(RTRIM(s.OrigClaimNo)) AND LTRIM(RTRIM(c.OrigClaimNo)) <> ''");
        })->selectRaw("c.ClaimNo as record_key, COALESCE(NULLIF(LTRIM(RTRIM(c.OrigClaimNo)), ''), c.ClaimNo) as reference, c.CoverNo as cover, COALESCE(NULLIF(c.InsuredName, ''), s.InsuredName) as label, c.CedCode as company_code, ".$this->claimDate()." as display_date, COALESCE(NULLIF(c.MStatusDesc, ''), NULLIF(c.MStatusCode, ''), 'Not available') as status, c.ClaimCurrencyCode as currency, c.ClaimAmt as amount, 'claim' as record_kind, c.ClaimNo as claim_number, s.submission_reference as portal_reference");
        // A submission appears on its own only until its reference is in the mirror.
        $submitted = DB::table('claim_submissions as s')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('claims as c')->whereColumn('c.CedCode', 's.company_code')
                ->whereRaw("LTRIM(RTRIM(c.OrigClaimNo)) = LTRIM(RTRIM(s.OrigClaimNo)) AND LTRIM(RTRIM(c.OrigClaimNo)) <> ''");
        })->selectRaw('s.submission_reference as record_key, s.OrigClaimNo as reference, s.CoverNo as cover, s.InsuredName as label, s.company_code, '.$this->date('COALESCE(s.submitted_at, s.created_at)')." as display_date, 'Submitted' as status, NULL as currency, NULL as amount, 'submission' as record_kind, NULL as claim_number, s.submission_reference as portal_reference");

        return [$submitted, $mirrored];
    }

    private function adjustments(): array
    {
        $submitted = DB::table('premium_adjustment_submissions as s')
            ->selectRaw('s.submission_reference as record_key, s.CoverNo as reference, s.CoverNo as cover, s.details as label, s.company_code, '.$this->date('COALESCE(s.submitted_at, s.created_at)')." as display_date, 'Submitted' as status, NULL as currency, NULL as amount, 'submission' as record_kind, NULL as claim_number, s.submission_reference as portal_reference");
        // Cover ownership takes precedence; a missing cover can use the source cedant code.
        // Preserve every adjustment item; CoverNo is not a unique document/submission key.
        $mirrored = DB::table('premium_adjustments as a')->leftJoin('covers as c', 'c.CoverNo', '=', 'a.CoverNo')
            ->leftJoinSub(DB::table('profit_commissions')->select('DocNo')->selectRaw('MAX(InvoiceAmount) as invoice_amount')->groupBy('DocNo')->havingRaw('COUNT(*) = 1'), 'header', 'header.DocNo', '=', 'a.DocumentNo')
            ->selectRaw('CAST(a.id AS VARCHAR(50)) as record_key, a.DocumentNo as reference, a.CoverNo as cover, COALESCE(a.ItemDesc, a.ItemNo) as label, CASE WHEN c.id IS NOT NULL THEN c.CusCode ELSE a.CedantAccountNo END as company_code, '.$this->date('a.DocumentDate')." as display_date, 'Not available' as status, a.DocCurrency as currency, header.invoice_amount as amount, 'adjustment' as record_kind, NULL as claim_number, NULL as portal_reference");

        return [$submitted, $mirrored];
    }

    private function date(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlsrv' => "CONVERT(varchar(10), {$column}, 23)",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM-DD')",
            default => "strftime('%Y-%m-%d', {$column})",
        };
    }

    private function claimDate(): string
    {
        $column = "COALESCE(NULLIF(c.DateRegistered, ''), NULLIF(c.DateReported, ''), NULLIF(c.CreatedOn, ''))";
        $compact = DB::connection()->getDriverName() === 'sqlsrv'
            ? "SUBSTRING({$column}, 1, 4) + '-' + SUBSTRING({$column}, 5, 2) + '-' + SUBSTRING({$column}, 7, 2)"
            : "SUBSTRING({$column}, 1, 4) || '-' || SUBSTRING({$column}, 5, 2) || '-' || SUBSTRING({$column}, 7, 2)";
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            $compact = "CONCAT(SUBSTRING({$column}, 1, 4), '-', SUBSTRING({$column}, 5, 2), '-', SUBSTRING({$column}, 7, 2))";
        }

        return "CASE WHEN {$column} LIKE '________' THEN {$compact} WHEN {$column} LIKE '____-__-__%' THEN SUBSTRING({$column}, 1, 10) ELSE ".$this->date('c.created_at').' END';
    }
}
