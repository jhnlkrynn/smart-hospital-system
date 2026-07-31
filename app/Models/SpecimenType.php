<?php

namespace App\Models;

use Database\Factories\SpecimenTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecimenType extends Model
{
    /** @use HasFactory<SpecimenTypeFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function laboratoryTests(): HasMany
    {
        return $this->hasMany(LaboratoryTest::class);
    }
}
