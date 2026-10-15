<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgedCedantPremium extends Model
{
    protected $table = 'aged_cedant_premiums';

    protected $fillable = [
        'DetailID', 'DocumentNo', 'DocumentDate', 'DocumentDateNo', 'AccountTypeCode', 'AccountType',
        'MAmount', 'SettledAmount', 'PendingAmount', 'CurrencyCode', 'CurrencyName', 'ExchangeRate',
        'FunctionalAmount', 'RiskNoteRef', 'Insured', 'RiskDetails', 'CategoryType', 'CategoryName',
        'CedantCode', 'CedantName', 'DocumentTypeCode', 'DocumentType', 'PercentPaid', 'Brokerage',
        'ReinsurersAmount', 'CollectionRemark', 'Reversed', 'ReversingDoc', 'CRate', 'PostingSEQ',
        'Revalued2021', 'RevaluationRate2021', 'Picked', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DetailID' => 'integer', 'DocumentDate' => 'datetime', 'DocumentDateNo' => 'integer',
            'MAmount' => 'decimal:8', 'SettledAmount' => 'decimal:8', 'PendingAmount' => 'decimal:8',
            'ExchangeRate' => 'decimal:8', 'FunctionalAmount' => 'decimal:8', 'PercentPaid' => 'decimal:8',
            'Brokerage' => 'decimal:8', 'ReinsurersAmount' => 'decimal:8', 'CRate' => 'decimal:8',
            'Reversed' => 'integer', 'PostingSEQ' => 'integer', 'Revalued2021' => 'decimal:8',
            'RevaluationRate2021' => 'decimal:8', 'Picked' => 'integer', 'last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CedantCode', 'CmpCode');
    }
}
