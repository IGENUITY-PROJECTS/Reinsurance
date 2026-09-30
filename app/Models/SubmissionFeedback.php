<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SubmissionFeedback extends Model
{
    protected $table = 'submission_feedback';

    protected $fillable = ['message'];

    protected static function booted(): void
    {
        static::saving(function (self $feedback) {
            $hasClaim = $feedback->claim_submission_id !== null;
            $hasAdjustment = $feedback->premium_adjustment_submission_id !== null;
            if ($hasClaim === $hasAdjustment) {
                throw ValidationException::withMessages(['submission' => 'Feedback must belong to exactly one submission.']);
            }
            if (($hasClaim && ! $feedback->claimSubmission()->exists())
                || ($hasAdjustment && ! $feedback->premiumAdjustmentSubmission()->exists())) {
                throw ValidationException::withMessages(['submission' => 'The submission does not exist.']);
            }
            $feedback->message = trim((string) $feedback->message);
            if ($feedback->message === '') {
                throw ValidationException::withMessages(['message' => 'Enter a feedback message.']);
            }
        });
    }

    public function claimSubmission(): BelongsTo
    {
        return $this->belongsTo(ClaimSubmission::class, 'claim_submission_id');
    }

    public function premiumAdjustmentSubmission(): BelongsTo
    {
        return $this->belongsTo(PremiumAdjustmentSubmission::class, 'premium_adjustment_submission_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
