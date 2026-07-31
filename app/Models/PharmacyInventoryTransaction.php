<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyInventoryTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'transaction_type' => InventoryTransactionType::class,
            'quantity' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'occurred_at' => 'datetime',
        ];
    }

    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function stockBatch(): BelongsTo { return $this->belongsTo(MedicationStockBatch::class, 'medication_stock_batch_id')->withTrashed(); }
    public function location(): BelongsTo { return $this->belongsTo(PharmacyLocation::class, 'pharmacy_location_id')->withTrashed(); }
}
