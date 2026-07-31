<?php

namespace App\Models;

use App\Enums\PatientDocumentType;
use Database\Factories\PatientDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientDocument extends Model
{
    /** @use HasFactory<PatientDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['storage_path', 'stored_filename'];

    protected function casts(): array
    {
        return [
            'document_type' => PatientDocumentType::class,
            'is_confidential' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
