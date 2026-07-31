<?php

namespace App\Models;

use App\Enums\SpecimenStatus;
use Database\Factories\LaboratorySpecimenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratorySpecimen extends Model
{
    /** @use HasFactory<LaboratorySpecimenFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => SpecimenStatus::class,
            'collected_at' => 'datetime',
            'received_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'recollection_due_at' => 'datetime',
        ];
    }

    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function specimenType(): BelongsTo
    {
        return $this->belongsTo(SpecimenType::class)->withTrashed();
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(LaboratoryRequestItem::class, 'laboratory_specimen_items')->withTimestamps();
    }
}
