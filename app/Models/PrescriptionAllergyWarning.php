<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionAllergyWarning extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['requires_acknowledgment' => 'boolean', 'acknowledged_at' => 'datetime']; }
    public function prescription(): BelongsTo { return $this->belongsTo(Prescription::class); }
    public function item(): BelongsTo { return $this->belongsTo(PrescriptionItem::class, 'prescription_item_id'); }
    public function medication(): BelongsTo { return $this->belongsTo(Medication::class)->withTrashed(); }
    public function patientAllergy(): BelongsTo { return $this->belongsTo(PatientAllergy::class); }
}
