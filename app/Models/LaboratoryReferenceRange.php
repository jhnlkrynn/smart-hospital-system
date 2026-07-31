<?php

namespace App\Models;

use Database\Factories\LaboratoryReferenceRangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryReferenceRange extends Model
{
    /** @use HasFactory<LaboratoryReferenceRangeFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'lower_bound' => 'decimal:4',
            'upper_bound' => 'decimal:4',
            'critical_lower_bound' => 'decimal:4',
            'critical_upper_bound' => 'decimal:4',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function laboratoryTest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTest::class)->withTrashed();
    }
}
