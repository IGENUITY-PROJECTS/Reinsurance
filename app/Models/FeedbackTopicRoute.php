<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackTopicRoute extends Model
{
    protected $primaryKey = 'topic';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['topic', 'department_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
