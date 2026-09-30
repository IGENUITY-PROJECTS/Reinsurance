<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverReinsurer extends Model
{
    protected $fillable = [
        'DetailID',
        'CedName',
        'ReinName',
        'ReinPostal',
        'ReinTown',
        'ReinCtryCode',
        'ReinCtry',
        'ReinMktCode',
        'ReinMktDesc',
        'ShrAlloc',
        'ShrComp',
        'ShrOpt',
        'BrkgComp',
        'MTaxable',
        'MTaxCompShr',
        'MTaxOptShr',
        'RskInsured',
        'CompAmt',
        'OptAmt',
        'TotalAmt',
        'BrkgOpt',
        'CommissionOverWride',
        'BrokerageOverWride',
        'BrokerageComp',
        'BrokerageOpt',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'DetailID' => 'integer',
            'ShrAlloc' => 'double',
            'ShrComp' => 'double',
            'ShrOpt' => 'double',
            'BrkgComp' => 'double',
            'MTaxable' => 'integer',
            'MTaxCompShr' => 'integer',
            'MTaxOptShr' => 'integer',
            'RskInsured' => 'double',
            'CompAmt' => 'double',
            'OptAmt' => 'double',
            'TotalAmt' => 'double',
            'BrkgOpt' => 'double',
            'CommissionOverWride' => 'double',
            'BrokerageOverWride' => 'double',
            'BrokerageComp' => 'double',
            'BrokerageOpt' => 'double',
            'last_synced_at' => 'datetime',
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Cover::class, 'CoverNo', 'CoverNo');
    }

    public function cedant(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CedCode', 'CmpCode');
    }

    public function reinsurer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'ReinCode', 'CmpCode');
    }
}
