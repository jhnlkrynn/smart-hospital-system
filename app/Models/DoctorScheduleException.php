<?php

namespace App\Models;

use App\Enums\ScheduleExceptionType;
use Database\Factories\DoctorScheduleExceptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorScheduleException extends Model
{
    /** @use HasFactory<DoctorScheduleExceptionFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'exception_date' => 'date',
            'exception_type' => ScheduleExceptionType::class,
            'is_available' => 'boolean',
            'maximum_appointments' => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_employee_id')->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForDoctor(Builder $query, mixed $doctorId): Builder
    {
        return $query->where('doctor_employee_id', $doctorId);
    }

    public function scopeForDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('exception_date', $date);
    }

    public function scopeUnavailable(Builder $query): Builder
    {
        return $query->where('is_available', false);
    }
}
