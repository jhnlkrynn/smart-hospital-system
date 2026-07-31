<?php

namespace App\Models;

use Database\Factories\DiagnosisCatalogFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiagnosisCatalog extends Model
{
    /** @use HasFactory<DiagnosisCatalogFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'diagnosis_catalog';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_patient_visible_default' => 'boolean',
        ];
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(ConsultationDiagnosis::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function code(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => $value ? strtoupper(trim($value)) : null);
    }
}
