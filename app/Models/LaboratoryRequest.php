<?php

namespace App\Models;

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryRequestStatus;
use Database\Factories\LaboratoryRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryRequest extends Model
{
    /** @use HasFactory<LaboratoryRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'priority' => LaboratoryPriority::class,
            'status' => LaboratoryRequestStatus::class,
            'requested_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requesting_doctor_employee_id')->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class);
    }

    public function specimens(): HasMany
    {
        return $this->hasMany(LaboratorySpecimen::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(fn (Builder $query) => $query
            ->where('request_number', 'like', "%{$search}%")
            ->orWhereHas('patient', fn (Builder $query) => $query->where('patient_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")));
    }
}
