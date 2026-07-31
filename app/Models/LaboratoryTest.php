<?php

namespace App\Models;

use App\Enums\LaboratoryResultType;
use Database\Factories\LaboratoryTestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryTest extends Model
{
    /** @use HasFactory<LaboratoryTestFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'result_type' => LaboratoryResultType::class,
            'requires_fasting' => 'boolean',
            'requires_verification' => 'boolean',
            'is_panel' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCategory::class, 'laboratory_test_category_id')->withTrashed();
    }

    public function specimenType(): BelongsTo
    {
        return $this->belongsTo(SpecimenType::class)->withTrashed();
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'laboratory_test_components', 'parent_test_id', 'component_test_id')
            ->withPivot(['display_order', 'is_required'])
            ->withTimestamps()
            ->orderBy('laboratory_test_components.display_order');
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(LaboratoryReferenceRange::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
