<?php

namespace App\Models;

use Database\Factories\LaboratoryTestCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryTestCategory extends Model
{
    /** @use HasFactory<LaboratoryTestCategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tests(): HasMany
    {
        return $this->hasMany(LaboratoryTest::class);
    }
}
