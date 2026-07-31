<?php

namespace App\Models;

use App\Enums\AllergySeverity;
use App\Enums\AllergyType;
use Database\Factories\PatientAllergyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientAllergy extends Model
{
    /** @use HasFactory<PatientAllergyFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'recorded_by'];

    protected function casts(): array
    {
        return [
            'allergy_type' => AllergyType::class,
            'severity' => AllergySeverity::class,
            'diagnosed_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
