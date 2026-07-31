<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationAllergyGroup extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function medications(): BelongsToMany { return $this->belongsToMany(Medication::class, 'medication_allergy_group_items')->withTimestamps(); }
}
