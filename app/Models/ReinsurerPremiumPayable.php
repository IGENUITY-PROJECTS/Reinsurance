<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinsurerPremiumPayable extends Model
{
    protected $table = 'reinsurer_premium_payables';

    protected $fillable = [
        'DetailID', 'DocumentNo', 'DocumentDate', 'DocumentDateNo', 'AccountTypeCode', 'AccountType',
        'MAmount', 'SettledAmount', 'PendingAmount', 'CurrencyCode', 'CurrencyName', 'ExchangeRate',
        'FunctionalAmount', 'RiskNoteRef', 'Insured', 'RiskDetails', 'CategoryType', 'CategoryName',
        'ReinsureCode', 'ReinsurerName', 'DocumentTypeCode', 'DocumentType', 'CedantDoc', 'PercentPaid',
        'CedantCode', 'CedantName', 'RequisitionNo', 'Reversed', 'ReversingDoc', 'CRate', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DetailID' => 'integer', 'DocumentDate' => 'datetime', 'DocumentDateNo' => 'integer',
            'MAmount' => 'decimal:8', 'SettledAmount' => 'decimal:8', 'PendingAmount' => 'decimal:8',
            'ExchangeRate' => 'decimal:8', 'FunctionalAmount' => 'decimal:8', 'PercentPaid' => 'decimal:8',
            'Reversed' => 'integer', 'CRate' => 'decimal:8', 'last_synced_at' => 'datetime',
        ];
    }

    public function reinsurer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'ReinsureCode', 'CmpCode');
    }

    public function cedant(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CedantCode', 'CmpCode');
    }
}
