<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cover extends Model
{
    protected $fillable = [
        'CoverNo',
        'RecNo',
        'MRef',
        'MTitle',
        'BizType',
        'CusName',
        'CVStartDate',
        'CVEndDate',
        'CVDocDate',
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
        'InsCode',
        'InsName',
        'CVDetails',
        'MStatus',
        'RskInsured',
        'OrigCoverNo',
        'MReinsurers',
        'MPremium',
        'MBrokerage',
        'MCurrency',
        'MShare',
        'CashCallLimitAmount',
        'CashCallExcessOff',
        'CoverAmount',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'RecNo' => 'integer',
            'MStatus' => 'integer',
            'RskInsured' => 'double',
            'MPremium' => 'double',
            'MBrokerage' => 'double',
            'MShare' => 'double',
            'CashCallLimitAmount' => 'double',
            'CashCallExcessOff' => 'double',
            'CoverAmount' => 'double',
            'last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CusCode', 'CmpCode');
    }

    public function reinsurers(): HasMany
    {
        return $this->hasMany(CoverReinsurer::class, 'CoverNo', 'CoverNo');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'CoverNo', 'CoverNo');
    }

    public function claimSubmissions(): HasMany
    {
        return $this->hasMany(ClaimSubmission::class, 'CoverNo', 'CoverNo');
    }

    public function getRouteKeyName(): string
    {
        return 'CoverNo';
    }
}
