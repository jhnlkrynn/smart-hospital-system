<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Database\Factories\DoctorScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'slot_duration_minutes' => 'integer',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor(Builder $query, mixed $doctorId): Builder
    {
        return $query->where('doctor_employee_id', $doctorId);
    }

    public function scopeForDay(Builder $query, DayOfWeek|string $day): Builder
    {
        return $query->where('day_of_week', $day instanceof DayOfWeek ? $day->value : $day);
    }

    public function scopeEffectiveOn(Builder $query, mixed $date): Builder
    {
        return $query->where(function (Builder $query) use ($date): void {
            $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date);
        })->where(function (Builder $query) use ($date): void {
            $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $date);
        });
    }
}
