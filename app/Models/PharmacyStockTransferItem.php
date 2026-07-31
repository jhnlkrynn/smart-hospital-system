<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStockTransferItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function transfer(): BelongsTo { return $this->belongsTo(PharmacyStockTransfer::class, 'pharmacy_stock_transfer_id'); }
    public function stockBatch(): BelongsTo { return $this->belongsTo(MedicationStockBatch::class, 'medication_stock_batch_id')->withTrashed(); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
}
