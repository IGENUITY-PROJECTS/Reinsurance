<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    protected $fillable = [
        'ClaimNo',
        'ClaimRec',
        'ClaimType',
        'RelatedNo',
        'RelatedRef',
        'ClaimRef',
        'CoverNo',
        'CoverRef',
        'CedCode',
        'CedName',
        'OrigPolicy',
        'OrigClaimNo',
        'InsuredName',
        'LossPeriod',
        'DateLoss',
        'DateRegistered',
        'LossLocation',
        'MStatusCode',
        'MStatusDesc',
        'LossDetails',
        'Comments',
        'CreatedOn',
        'CusCode',
        'CusName',
        'CVStartDate',
        'CVEndDate',
        'CVYear',
        'REDivCode',
        'REDivDesc',
        'RETypeCode',
        'RETypeDesc',
        'REGrpCode',
        'REGrpDesc',
        'RECatCode',
        'RECatDesc',
        'RETreatyTypCode',
        'RETreatyTypDesc',
        'REClassCode',
        'REClassDesc',
        'AcctCode',
        'AcctName',
        'AcctPostal',
        'AcctTown',
        'AcctCtryCode',
        'AcctCtryDesc',
        'AcctMktCode',
        'AcctMktDesc',
        'ClaimAmt',
        'BrkClaimAmt',
        'TreatyText',
        'LossReserve',
        'CedantRetention',
        'DateReported',
        'LogicalClaimAmount',
        'OldDocument',
        'ClaimCurrencyCode',
        'ClaimCurrencyName',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'ClaimRec' => 'integer',
            'ClaimAmt' => 'double',
            'BrkClaimAmt' => 'double',
            'LossReserve' => 'double',
            'CedantRetention' => 'double',
            'LogicalClaimAmount' => 'double',
            'OldDocument' => 'integer',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(ClaimSubmission::class, 'ClaimNo', 'ClaimNo');
    }

    public function getRouteKeyName(): string
    {
        return 'ClaimNo';
    }
}
