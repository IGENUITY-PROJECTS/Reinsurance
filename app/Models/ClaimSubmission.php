<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClaimSubmission extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_AWAITING_DOCUMENTS = 'awaiting_documents';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REGISTERED_IN_RBS = 'registered_in_rbs';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'OrigPolicy', 'OrigClaimNo', 'InsuredName', 'LossPeriod', 'DateLoss',
        'LossLocation', 'LossDetails', 'Comments', 'ClaimAmt', 'DateReported',
        'ClaimCurrencyCode', 'ClaimCurrencyName',
    ];

    protected function casts(): array
    {
        return [
            'ClaimAmt' => 'decimal:8',
            'submitted_at' => 'datetime',
            'linked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $submission) {
            $reference = trim((string) $submission->OrigClaimNo);
            // Once submitted or linked, the shared RBS reference must stay stable.
            if ($submission->exists && ($submission->getRawOriginal('submitted_at') !== null
                || $submission->getRawOriginal('ClaimNo') !== null)
                && $reference !== $submission->getRawOriginal('OrigClaimNo')) {
                throw ValidationException::withMessages(['OrigClaimNo' => 'The reference cannot change after submission or RBS linking.']);
            }
            $submission->OrigClaimNo = $reference !== '' ? $reference : 'CLM-'.Str::ulid();
            if (mb_strlen($submission->OrigClaimNo) > 50) {
                throw ValidationException::withMessages(['OrigClaimNo' => 'The reference must not exceed 50 characters.']);
            }
        });
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

    // Relationship to the official claim; reference-linking workflow will be added later.
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class, 'ClaimNo', 'ClaimNo');
    }

    public function getRbsStatusAttribute(): string
    {
        $claim = $this->claim;
        if (! $claim || $claim->CedCode !== $this->company_code
            || ($this->CoverNo && $claim->CoverNo !== $this->CoverNo)) {
            return 'Submitted';
        }

        return $claim->MStatusDesc ?: ($claim->MStatusCode ?: 'Not available');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'id');
    }

    public function linkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by', 'id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class, 'claim_submission_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ClaimStatusHistory::class, 'claim_submission_id')->orderBy('changed_at')->orderBy('id');
    }

    public function requiresDocuments(): bool
    {
        return true;
    }

    public function getRouteKeyName(): string
    {
        return 'submission_reference';
    }
}
