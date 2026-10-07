<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitCommissionDetail extends Model
{
    protected $fillable = [
        'DetailID',
        'DocumentNo',
        'ItemCode',
        'ItemDescription',
        'MOperator',
        'Amount',
        'PrintOrder',
        'MRate',
        'CompanyOption',
        'CompanyCode',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DetailID' => 'integer',
            'MOperator' => 'integer',
            'Amount' => 'decimal:8',
            'PrintOrder' => 'integer',
            'MRate' => 'decimal:8',
            'CompanyOption' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function profitCommission(): BelongsTo
    {
        return $this->belongsTo(ProfitCommission::class, 'DocumentNo', 'DocNo');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyCode', 'CmpCode');
    }
}
