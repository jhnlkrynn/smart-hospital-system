<?php

namespace App\Services\Consultation;

use App\Models\Consultation;
use App\Models\ConsultationAttachment;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\UploadedFile;

class ConsultationAttachmentService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function upload(Consultation $consultation, UploadedFile $file, array $data, User $actor): ConsultationAttachment
    {
        $path = $file->store('consultation-attachments/'.$consultation->id);

        $attachment = ConsultationAttachment::create([
            'consultation_id' => $consultation->id,
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

        $this->audit->record($actor, 'consultation_attachment.uploaded', 'clinical-attachments', $attachment, 'Consultation attachment uploaded.');

        return $attachment;
    }
}
