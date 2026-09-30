<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{
    protected $fillable = [
        'CmpCode',
        'CmpSubTypeCode',
        'CmpSubTypeDesc',
        'CmpTypeCode',
        'CmpTypeDesc',
        'CompanyName',
        'ShortName',
        'PostalAdd',
        'MTown',
        'CountryCode',
        'CountryName',
        'MktZoneCode',
        'MktZoneDesc',
        'MFax',
        'EmailAdd',
        'Website',
        'StartDate',
        'CreatedOn',
        'MActive',
        'ZoneCode',
        'ZoneName',
        'NatAcNo',
        'NatAcName',
        'PinNo',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'MActive' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function scopeCedants(Builder $query): Builder
    {
        return $query->where('CmpSubTypeCode', '100');
    }

    public function covers(): HasMany
    {
        return $this->hasMany(Cover::class, 'CusCode', 'CmpCode');
    }

    public function claimSubmissions(): HasMany
    {
        return $this->hasMany(ClaimSubmission::class, 'company_code', 'CmpCode');
    }

    public function premiums(): HasMany
    {
        return $this->hasMany(CedantPremium::class, 'CedantCode', 'CmpCode');
    }

    public function claimPayables(): HasMany
    {
        return $this->hasMany(CedantClaimPayable::class, 'CedantCode', 'CmpCode');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
