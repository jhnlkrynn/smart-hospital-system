<?php

namespace App\Models;

use Database\Factories\ConsultationAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultationAttachment extends Model
{
    /** @use HasFactory<ConsultationAttachmentFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['storage_path', 'stored_filename'];

    protected function casts(): array
    {
        return [
            'is_confidential' => 'boolean',
            'is_patient_visible' => 'boolean',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
