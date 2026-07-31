<?php

namespace App\Models;

use App\Enums\LaboratoryAbnormalFlag;
use App\Enums\LaboratoryResultType;
use Database\Factories\LaboratoryResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryResult extends Model
{
    /** @use HasFactory<LaboratoryResultFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'result_type' => LaboratoryResultType::class,
            'numeric_value' => 'decimal:4',
            'boolean_value' => 'boolean',
            'structured_value' => 'array',
            'abnormal_flag' => LaboratoryAbnormalFlag::class,
            'is_critical' => 'boolean',
            'is_patient_visible' => 'boolean',
            'entered_at' => 'datetime',
            'verified_at' => 'datetime',
            'released_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequestItem::class, 'laboratory_request_item_id')->withTrashed();
    }

    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function laboratoryTest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTest::class)->withTrashed();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LaboratoryResultVersion::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LaboratoryResultAttachment::class);
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(LaboratoryResultAcknowledgment::class);
    }
}
