<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackMessage extends Model
{
    protected $fillable = ['message'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(FeedbackConversation::class, 'feedback_conversation_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
