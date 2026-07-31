<?php

namespace App\Models;

use App\Enums\PrescriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected function casts(): array { return ['status' => PrescriptionStatus::class, 'valid_from' => 'date', 'valid_until' => 'date', 'finalized_at' => 'datetime', 'reviewed_at' => 'datetime', 'cancelled_at' => 'datetime']; }
    public function consultation(): BelongsTo { return $this->belongsTo(Consultation::class)->withTrashed(); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class)->withTrashed(); }
    public function doctor(): BelongsTo { return $this->belongsTo(Employee::class, 'doctor_employee_id')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(PrescriptionItem::class); }
    public function allergyWarnings(): HasMany { return $this->hasMany(PrescriptionAllergyWarning::class); }
    public function reservations(): HasMany { return $this->hasMany(PharmacyStockReservation::class); }
}
