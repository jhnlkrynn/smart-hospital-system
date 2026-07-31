<?php

namespace App\Models;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['formatted_date', 'formatted_time_range', 'status_label'];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
            'source' => AppointmentSource::class,
            'cancelled_at' => 'datetime',
            'approved_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
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

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class)->withTrashed();
    }

    public function parentAppointment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_appointment_id')->withTrashed();
    }

    public function rescheduledAppointments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_appointment_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function queue(): HasOne
    {
        return $this->hasOne(PatientQueue::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            AppointmentStatus::Rejected->value,
            AppointmentStatus::Cancelled->value,
            AppointmentStatus::Rescheduled->value,
            AppointmentStatus::NoShow->value,
        ]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', now('Asia/Manila')->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '<', now('Asia/Manila')->toDateString());
    }

    public function scopeForDoctor(Builder $query, mixed $doctorId): Builder
    {
        return $query->where('doctor_employee_id', $doctorId);
    }

    public function scopeForPatient(Builder $query, mixed $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeByStatus(Builder $query, mixed $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeBetweenDates(Builder $query, mixed $from, mixed $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate('appointment_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('appointment_date', '<=', $to));
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('appointment_number', 'like', "%{$search}%")
                ->orWhere('reason_for_visit', 'like', "%{$search}%")
                ->orWhereHas('patient', fn (Builder $query) => $query->where('patient_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                ->orWhereHas('doctor', fn (Builder $query) => $query->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
        });
    }

    protected function formattedDate(): Attribute
    {
        return Attribute::get(fn (): string => $this->appointment_date?->timezone('Asia/Manila')->format('M d, Y') ?? '');
    }

    protected function formattedTimeRange(): Attribute
    {
        return Attribute::get(fn (): string => substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5));
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->status->label());
    }
}
