<?php

namespace App\Models;

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryResultType;
use App\Enums\LaboratoryTestItemStatus;
use Database\Factories\LaboratoryRequestItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryRequestItem extends Model
{
    /** @use HasFactory<LaboratoryRequestItemFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'result_type_snapshot' => LaboratoryResultType::class,
            'priority' => LaboratoryPriority::class,
            'status' => LaboratoryTestItemStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    public function laboratoryTest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTest::class)->withTrashed();
    }

    public function specimenType(): BelongsTo
    {
        return $this->belongsTo(SpecimenType::class)->withTrashed();
    }

    public function result(): HasOne
    {
        return $this->hasOne(LaboratoryResult::class);
    }
}
