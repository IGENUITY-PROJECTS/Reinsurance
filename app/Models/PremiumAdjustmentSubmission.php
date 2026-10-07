<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PremiumAdjustmentSubmission extends Model
{
    protected $fillable = ['details'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission) {
            $submission->submission_reference ??= (string) Str::ulid();
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_code', 'CmpCode');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Cover::class, 'CoverNo', 'CoverNo');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PremiumAdjustmentDocument::class, 'premium_adjustment_submission_id');
    }

    public function getRouteKeyName(): string
    {
        return 'submission_reference';
    }

    public function premiumAdjustments(): HasMany
    {
        return $this->hasMany(PremiumAdjustment::class, 'CoverNo', 'CoverNo');
    }
}
