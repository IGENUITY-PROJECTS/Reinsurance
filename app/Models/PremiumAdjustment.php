<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumAdjustment extends Model
{
    protected $fillable = ['DocumentNo', 'RskNo', 'CoverNo', 'CoverNoDesc', 'CedantAccountNo', 'Cedant', 'DocCurrency', 'AccountType', 'AccountTypeDesc', 'LastUser', 'ItemNo', 'ItemDesc', 'Remarks', 'MinDepositPremium', 'PremiumAdjRate', 'NetAccountedPremium', 'PremiumComputed', 'Claims', 'MinCost', 'MaxCost', 'LoadingFactor', 'LoadingFactorDiv', 'EffectiveRate', 'CedCommRate', 'PremiumSplit', 'HasBurningCost', 'MOperator', 'PrintOrder', 'Posted', 'BasedOn', 'Committed', 'DeductMDP', 'DocumentDate', 'LastMaintained', 'last_synced_at'];

    protected function casts(): array
    {
        return [
            'MinDepositPremium' => 'double',
            'PremiumAdjRate' => 'double',
            'NetAccountedPremium' => 'double',
            'PremiumComputed' => 'double',
            'Claims' => 'double',
            'MinCost' => 'double',
            'MaxCost' => 'double',
            'LoadingFactor' => 'double',
            'LoadingFactorDiv' => 'double',
            'EffectiveRate' => 'double',
            'CedCommRate' => 'double',
            'PremiumSplit' => 'double',
            'HasBurningCost' => 'integer',
            'MOperator' => 'integer',
            'PrintOrder' => 'integer',
            'Posted' => 'integer',
            'BasedOn' => 'integer',
            'Committed' => 'integer',
            'DeductMDP' => 'integer',
            'DocumentDate' => 'datetime',
            'LastMaintained' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Cover::class, 'CoverNo', 'CoverNo');
    }
}
