<?php

namespace App\Models;

use App\Enums\QueuePriority;
use App\Enums\QueueStatus;
use App\Enums\VisitType;
use Database\Factories\PatientQueueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientQueue extends Model
{
    /** @use HasFactory<PatientQueueFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'queues';

    protected $guarded = ['id'];

    protected $appends = ['waiting_minutes', 'priority_label', 'status_label'];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'status' => QueueStatus::class,
            'priority' => QueuePriority::class,
            'visit_type' => VisitType::class,
            'is_emergency' => 'boolean',
            'is_senior_citizen' => 'boolean',
            'is_pwd' => 'boolean',
            'is_pregnant' => 'boolean',
            'checked_in_at' => 'datetime',
            'called_at' => 'datetime',
            'triage_started_at' => 'datetime',
            'triage_completed_at' => 'datetime',
            'doctor_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'no_show_at' => 'datetime',
        ];
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

    public function histories(): HasMany
    {
        return $this->hasMany(QueueStatusHistory::class, 'queue_id');
    }

    public function triageRecord(): HasOne
    {
        return $this->hasOne(TriageRecord::class, 'queue_id');
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class, 'queue_id');
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class, 'queue_entry_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('queue_date', now('Asia/Manila')->toDateString());
    }

    public function scopeForDepartment(Builder $query, mixed $departmentId): Builder
    {
        return $departmentId ? $query->where('department_id', $departmentId) : $query;
    }

    public function scopeForDoctor(Builder $query, mixed $doctorId): Builder
    {
        return $doctorId ? $query->where('doctor_employee_id', $doctorId) : $query;
    }

    public function scopeByStatus(Builder $query, mixed $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeWaitingOrder(Builder $query): Builder
    {
        return $query->orderByRaw("case priority when 'emergency' then 1 when 'pregnant' then 2 when 'pwd' then 3 when 'senior_citizen' then 4 else 5 end")
            ->orderBy('checked_in_at')
            ->orderBy('id');
    }

    protected function waitingMinutes(): Attribute
    {
        return Attribute::get(fn (): int => (int) ($this->checked_in_at?->diffInMinutes($this->completed_at ?? now()) ?? 0));
    }

    protected function priorityLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->priority->label());
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->status->label());
    }
}
