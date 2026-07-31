<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueStatusHistory extends Model
{
    protected $guarded = ['id'];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PatientQueue::class, 'queue_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
