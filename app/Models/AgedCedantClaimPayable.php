<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgedCedantClaimPayable extends Model
{
    protected $table = 'aged_cedant_claim_payables';

    protected $fillable = [
        'DetailID', 'DocumentNo', 'DocumentDate', 'DocumentDateNo', 'AccountTypeCode', 'AccountType',
        'MAmount', 'SettledAmount', 'PendingAmount', 'CurrencyCode', 'CurrencyName', 'ExchangeRate',
        'FunctionalAmount', 'RiskNoteRef', 'Insured', 'RiskDetails', 'CategoryType', 'CategoryName',
        'CedantCode', 'CedantName', 'DocumentTypeCode', 'DocumentType', 'DOL', 'DOLNo', 'ClaimRefReinsured', 'ClaimReference',
        'PercentPaid', 'RequisitionNo', 'CollectionRemark', 'Reversed', 'ReversingDoc', 'CRate', 'PostingSeq',
        'Revalued2021', 'RevaluationRate2021', 'Picked', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DetailID' => 'integer', 'DocumentDate' => 'datetime', 'DocumentDateNo' => 'integer',
            'MAmount' => 'decimal:8', 'SettledAmount' => 'decimal:8', 'PendingAmount' => 'decimal:8',
            'ExchangeRate' => 'decimal:8', 'FunctionalAmount' => 'decimal:8', 'PercentPaid' => 'decimal:8',
            'DOL' => 'datetime', 'DOLNo' => 'integer', 'CRate' => 'decimal:8',
            'Reversed' => 'integer', 'PostingSeq' => 'integer', 'Revalued2021' => 'decimal:8',
            'RevaluationRate2021' => 'decimal:8', 'Picked' => 'integer', 'last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CedantCode', 'CmpCode');
    }
}
