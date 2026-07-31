<?php

namespace App\Models;

use App\Enums\PatientConditionStatus;
use Database\Factories\PatientConditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientCondition extends Model
{
    /** @use HasFactory<PatientConditionFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'recorded_by'];

    protected function casts(): array
    {
        return [
            'diagnosis_date' => 'date',
            'status' => PatientConditionStatus::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
