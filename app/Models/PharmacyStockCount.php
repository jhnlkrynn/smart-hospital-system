<?php

namespace App\Models;

use App\Enums\StockCountStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyStockCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => StockCountStatus::class, 'count_date' => 'date', 'reconciled_at' => 'datetime'];
    }

    public function location(): BelongsTo { return $this->belongsTo(PharmacyLocation::class, 'pharmacy_location_id')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(PharmacyStockCountItem::class); }
}
