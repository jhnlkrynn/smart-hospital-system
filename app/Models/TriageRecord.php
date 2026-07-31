<?php

namespace App\Models;

use App\Enums\TriageAcuity;
use Database\Factories\TriageRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TriageRecord extends Model
{
    /** @use HasFactory<TriageRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'acuity' => TriageAcuity::class,
            'pain_scale' => 'integer',
            'pregnancy_flag' => 'boolean',
            'fall_risk_score' => 'integer',
            'allergies_reviewed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PatientQueue::class, 'queue_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function vitalSign(): HasOne
    {
        return $this->hasOne(VitalSign::class);
    }

}
