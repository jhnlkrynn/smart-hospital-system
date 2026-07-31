<?php

namespace App\Models;

use App\Enums\PrescriptionItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected function casts(): array { return ['status' => PrescriptionItemStatus::class, 'quantity' => 'decimal:3', 'dose_quantity' => 'decimal:3']; }
    public function prescription(): BelongsTo { return $this->belongsTo(Prescription::class); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function reservations() { return $this->hasMany(PharmacyStockReservation::class); }
    public function reservationQuantity(): float { return (float) $this->reservations()->whereNull('released_at')->sum('quantity_reserved'); }
}
