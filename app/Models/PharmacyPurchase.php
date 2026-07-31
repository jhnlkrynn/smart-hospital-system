<?php

namespace App\Models;

use App\Enums\PharmacyPurchaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PharmacyPurchaseStatus::class,
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'received_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(PharmacySupplier::class, 'pharmacy_supplier_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyPurchaseItem::class);
    }
}
