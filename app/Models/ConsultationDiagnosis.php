<?php

namespace App\Models;

use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use Database\Factories\ConsultationDiagnosisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultationDiagnosis extends Model
{
    /** @use HasFactory<ConsultationDiagnosisFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'diagnosis_type' => DiagnosisType::class,
            'diagnosis_status' => DiagnosisStatus::class,
            'onset_date' => 'date',
            'resolved_date' => 'date',
            'is_patient_visible' => 'boolean',
            'sync_to_problem_list' => 'boolean',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function diagnosisCatalog(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCatalog::class)->withTrashed();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
