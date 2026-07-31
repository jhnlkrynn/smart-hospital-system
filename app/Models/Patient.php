<?php

namespace App\Models;

use App\Enums\AllergySeverity;
use App\Enums\PatientStatus;
use App\Enums\Sex;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'patient_number', 'qr_token', 'created_by', 'updated_by'];

    protected $hidden = ['qr_token'];

    protected $appends = ['full_name', 'age', 'profile_photo_url'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'registration_date' => 'date',
            'status' => PatientStatus::class,
            'sex' => Sex::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(PatientEmergencyContact::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PatientCondition::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(PatientQueue::class);
    }

    public function triageRecords(): HasMany
    {
        return $this->hasMany(TriageRecord::class);
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(PatientProblem::class);
    }

    public function medicalCertificates(): HasMany
    {
        return $this->hasMany(MedicalCertificate::class);
    }

    public function laboratoryRequests(): HasMany
    {
        return $this->hasMany(LaboratoryRequest::class);
    }

    public function laboratoryResults(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PatientStatus::Active->value);
    }

    public function scopeByStatus(Builder $query, mixed $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeRegisteredBetween(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate('registration_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('registration_date', '<=', $to));
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('patient_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('contact_number', 'like', "%{$search}%");
        });
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => collect([$this->first_name, $this->middle_name, $this->last_name, $this->suffix])->filter()->implode(' '));
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->date_of_birth?->age);
    }

    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=0f172a&color=fff');
    }

    protected function activeSevereAllergies(): Attribute
    {
        return Attribute::get(fn () => $this->allergies
            ->where('is_active', true)
            ->where('severity', AllergySeverity::Severe));
    }
}
