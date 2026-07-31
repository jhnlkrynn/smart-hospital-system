<?php

namespace App\Models;

use Database\Factories\LaboratoryResultAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryResultAttachment extends Model
{
    /** @use HasFactory<LaboratoryResultAttachmentFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['storage_path', 'stored_filename'];

    protected function casts(): array
    {
        return ['is_confidential' => 'boolean', 'is_patient_visible' => 'boolean'];
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(LaboratoryResult::class, 'laboratory_result_id');
    }
}
