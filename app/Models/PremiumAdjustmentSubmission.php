<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PremiumAdjustmentSubmission extends Model
{
    protected $fillable = ['adjustment_reference', 'details'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $submission) {
            $reference = trim((string) $submission->adjustment_reference);
            if ($submission->exists && $submission->getRawOriginal('submitted_at') !== null
                && $reference !== $submission->getRawOriginal('adjustment_reference')) {
                throw ValidationException::withMessages(['adjustment_reference' => 'The reference cannot change after submission.']);
            }
            $submission->adjustment_reference = $reference !== '' ? $reference : 'PA-'.Str::ulid();
            if (mb_strlen($submission->adjustment_reference) > 50) {
                throw ValidationException::withMessages(['adjustment_reference' => 'The reference must not exceed 50 characters.']);
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

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PremiumAdjustmentDocument::class, 'premium_adjustment_submission_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(SubmissionFeedback::class, 'premium_adjustment_submission_id')->orderBy('created_at')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'submission_reference';
    }
}
