<?php

namespace App\Models;

use App\Enums\StockBatchStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationStockBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = ['available_quantity'];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'quantity_on_hand' => 'decimal:3',
            'quantity_reserved' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'status' => StockBatchStatus::class,
            'quarantined_at' => 'datetime',
        ];
    }

    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function location(): BelongsTo { return $this->belongsTo(PharmacyLocation::class, 'pharmacy_location_id')->withTrashed(); }
    public function supplier(): BelongsTo { return $this->belongsTo(PharmacySupplier::class, 'pharmacy_supplier_id')->withTrashed(); }
    public function purchaseItem(): BelongsTo { return $this->belongsTo(PharmacyPurchaseItem::class, 'pharmacy_purchase_item_id'); }
    public function transactions(): HasMany { return $this->hasMany(PharmacyInventoryTransaction::class); }
    public function activeReservations(): HasMany { return $this->hasMany(PharmacyStockReservation::class)->whereNull('released_at'); }

    public function scopeReservable(Builder $query): Builder
    {
        return $query
            ->where('status', StockBatchStatus::Available->value)
            ->whereColumn('quantity_on_hand', '>', 'quantity_reserved')
            ->where(fn (Builder $query) => $query->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', now()->toDateString()));
    }

    protected function availableQuantity(): Attribute
    {
        return Attribute::get(fn (): string => number_format(max(0, (float) $this->quantity_on_hand - (float) $this->quantity_reserved), 3, '.', ''));
    }
}
