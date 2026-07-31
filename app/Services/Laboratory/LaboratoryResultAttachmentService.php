<?php

namespace App\Services\Laboratory;

use App\Models\LaboratoryResult;
use App\Models\LaboratoryResultAttachment;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\UploadedFile;

class LaboratoryResultAttachmentService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function upload(LaboratoryResult $result, UploadedFile $file, array $data, User $actor): LaboratoryResultAttachment
    {
        $path = $file->store('laboratory-result-attachments/'.$result->id);

        $attachment = LaboratoryResultAttachment::create([
            'laboratory_result_id' => $result->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => basename($path),
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'is_confidential' => (bool) ($data['is_confidential'] ?? false),
            'is_patient_visible' => (bool) ($data['is_patient_visible'] ?? false),
            'uploaded_by' => $actor->id,
        ]);

        $this->audit->record($actor, 'laboratory_attachment.uploaded', 'laboratory-attachments', $attachment, 'Laboratory result attachment uploaded.');

        return $attachment;
    }
}
