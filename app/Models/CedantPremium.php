<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CedantPremium extends Model
{
    // Override Laravel's automatic pluralization to cedant_premia.
    protected $table = 'cedant_premiums';

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
        'PercentPaid',
        'Brokerage',
        'ReinsurersAmount',
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
            'PercentPaid' => 'double',
            'Brokerage' => 'double',
            'ReinsurersAmount' => 'double',
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
