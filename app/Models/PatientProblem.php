<?php

namespace App\Models;

use App\Enums\DiagnosisStatus;
use Database\Factories\PatientProblemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientProblem extends Model
{
    /** @use HasFactory<PatientProblemFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => DiagnosisStatus::class,
            'onset_date' => 'date',
            'resolved_date' => 'date',
            'is_chronic' => 'boolean',
            'is_patient_visible' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function diagnosisCatalog(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCatalog::class)->withTrashed();
    }

    public function sourceDiagnosis(): BelongsTo
    {
        return $this->belongsTo(ConsultationDiagnosis::class, 'source_consultation_diagnosis_id')->withTrashed();
    }
}
