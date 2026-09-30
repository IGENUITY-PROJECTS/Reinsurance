<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CedantClaimPayable extends Model
{
    protected $table = 'cedant_claim_payables';

    protected $fillable = [
        'DocumentNo',
        'DocumentDate',
        'DocumentDateNo',
        'AccountTypeCode',
        'AccountType',
        'MAmount',
        'SettledAmount',
        'PendingAmount',
        'CurrencyCode',
        'CurrencyName',
        'ExchangeRate',
        'FunctionalAmount',
        'RiskNoteRef',
        'Insured',
        'RiskDetails',
        'LastUser',
        'LastMaintained',
        'LastMaintainedNo',
        'CategoryType',
        'CategoryName',
        'CedantCode',
        'CedantName',
        'DocumentTypeCode',
        'DocumentType',
        'DOL',
        'DOLNo',
        'ClaimRefReinsured',
        'ClaimReference',
        'PercentPaid',
        'RequisitionNo',
        'CollectionRemark',
        'Reversed',
        'ReversingDoc',
        'CRate',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DocumentDate' => 'datetime',
            'DocumentDateNo' => 'integer',
            'MAmount' => 'double',
            'SettledAmount' => 'double',
            'PendingAmount' => 'double',
            'ExchangeRate' => 'double',
            'FunctionalAmount' => 'double',
            'LastMaintained' => 'datetime',
            'LastMaintainedNo' => 'integer',
            'DOL' => 'datetime',
            'DOLNo' => 'integer',
            'PercentPaid' => 'double',
            'Reversed' => 'integer',
            'CRate' => 'double',
            'last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CedantCode', 'CmpCode');
    }
}
