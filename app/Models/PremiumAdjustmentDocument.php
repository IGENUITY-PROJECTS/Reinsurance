<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumAdjustmentDocument extends Model
{
    protected $fillable = [];

    protected $hidden = ['disk', 'path'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer'];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(PremiumAdjustmentSubmission::class, 'premium_adjustment_submission_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
