<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyStockTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function sourceLocation(): BelongsTo { return $this->belongsTo(PharmacyLocation::class, 'source_location_id')->withTrashed(); }
    public function destinationLocation(): BelongsTo { return $this->belongsTo(PharmacyLocation::class, 'destination_location_id')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(PharmacyStockTransferItem::class); }
}
