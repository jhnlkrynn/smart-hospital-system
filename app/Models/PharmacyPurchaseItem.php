<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyPurchaseItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['ordered_quantity' => 'decimal:3', 'received_quantity' => 'decimal:3', 'unit_cost' => 'decimal:4'];
    }

    public function purchase(): BelongsTo { return $this->belongsTo(PharmacyPurchase::class, 'pharmacy_purchase_id'); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function unit(): BelongsTo { return $this->belongsTo(MedicationUnit::class)->withTrashed(); }
    public function stockBatches(): HasMany { return $this->hasMany(MedicationStockBatch::class); }
}
