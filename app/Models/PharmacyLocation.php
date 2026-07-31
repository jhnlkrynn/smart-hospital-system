<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_quarantine' => 'boolean', 'is_active' => 'boolean'];
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(MedicationStockBatch::class);
    }
}
