<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use Database\Factories\ConsultationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    /** @use HasFactory<ConsultationFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['status_label', 'duration_minutes'];

    protected function casts(): array
    {
        return [
            'status' => ConsultationStatus::class,
            'started_at' => 'datetime',
            'paused_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reopened_at' => 'datetime',
            'follow_up_date' => 'date',
            'is_patient_visible' => 'boolean',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PatientQueue::class, 'queue_entry_id')->withTrashed();
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_employee_id')->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(ConsultationDiagnosis::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConsultationAttachment::class);
    }

    public function medicalCertificates(): HasMany
    {
        return $this->hasMany(MedicalCertificate::class);
    }

    public function laboratoryRequests(): HasMany
    {
        return $this->hasMany(LaboratoryRequest::class);
    }

    public function scopeForDoctor(Builder $query, mixed $doctorId): Builder
    {
        return $query->where('doctor_employee_id', $doctorId);
    }

    public function scopeForPatient(Builder $query, mixed $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus(Builder $query, mixed $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('consultation_number', 'like', "%{$search}%")
                ->orWhereHas('patient', fn (Builder $query) => $query->where('patient_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
        });
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->status->label());
    }

    protected function durationMinutes(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->started_at && $this->completed_at ? (int) $this->started_at->diffInMinutes($this->completed_at) : null);
    }
}
