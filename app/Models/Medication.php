<?php

namespace App\Models;

use App\Enums\MedicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $appends = ['display_name'];

    protected function casts(): array
    {
        return [
            'status' => MedicationStatus::class,
            'strength_value' => 'decimal:4',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'is_high_alert' => 'boolean',
            'requires_cold_storage' => 'boolean',
            'is_active' => 'boolean',
            'default_reorder_level' => 'decimal:3',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(MedicationCategory::class, 'medication_category_id')->withTrashed(); }
    public function dosageForm(): BelongsTo { return $this->belongsTo(DosageForm::class)->withTrashed(); }
    public function strengthUnit(): BelongsTo { return $this->belongsTo(MedicationUnit::class, 'strength_unit_id')->withTrashed(); }
    public function defaultRoute(): BelongsTo { return $this->belongsTo(MedicationRoute::class, 'default_route_id')->withTrashed(); }
    public function defaultFrequency(): BelongsTo { return $this->belongsTo(MedicationFrequency::class, 'default_frequency_id')->withTrashed(); }
    public function aliases(): HasMany { return $this->hasMany(MedicationAlias::class); }
    public function stockBatches(): HasMany { return $this->hasMany(MedicationStockBatch::class); }
    public function allergyGroups() { return $this->belongsToMany(MedicationAllergyGroup::class, 'medication_allergy_group_items')->withTimestamps(); }

    public function scopePrescribable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereIn('status', [MedicationStatus::Active->value, MedicationStatus::Restricted->value]);
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->generic_name.' '.($this->brand_name ? "({$this->brand_name})" : '').' '.($this->strength_value ? (string) $this->strength_value.' '.$this->strengthUnit?->symbol : '').' '.$this->dosageForm?->name));
    }
}
