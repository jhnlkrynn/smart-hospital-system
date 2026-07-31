<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStockCountItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['system_quantity' => 'decimal:3', 'physical_quantity' => 'decimal:3', 'variance_quantity' => 'decimal:3'];
    }

    public function stockCount(): BelongsTo { return $this->belongsTo(PharmacyStockCount::class, 'pharmacy_stock_count_id'); }
    public function stockBatch(): BelongsTo { return $this->belongsTo(MedicationStockBatch::class, 'medication_stock_batch_id')->withTrashed(); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
}
