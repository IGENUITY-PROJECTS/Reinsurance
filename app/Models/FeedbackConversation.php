<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class FeedbackConversation extends Model
{
    protected $fillable = ['subject'];

    protected function casts(): array
    {
        return ['needs_assignment' => 'boolean', 'period_from' => 'date', 'period_to' => 'date'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $conversation) {
            $conversation->reference ??= (string) Str::ulid();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_code', 'CmpCode');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FeedbackMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(FeedbackMessage::class)->latestOfMany();
    }
}
