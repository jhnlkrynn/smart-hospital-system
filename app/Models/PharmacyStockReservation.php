<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStockReservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantity_reserved' => 'decimal:3', 'reserved_at' => 'datetime', 'released_at' => 'datetime'];
    }

    public function prescription(): BelongsTo { return $this->belongsTo(Prescription::class); }
    public function prescriptionItem(): BelongsTo { return $this->belongsTo(PrescriptionItem::class); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function stockBatch(): BelongsTo { return $this->belongsTo(MedicationStockBatch::class, 'medication_stock_batch_id')->withTrashed(); }
}
