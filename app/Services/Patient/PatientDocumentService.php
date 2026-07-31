<?php

namespace App\Services\Patient;

use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientDocumentService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function upload(Patient $patient, UploadedFile $file, array $data, User $actor): PatientDocument
    {
        $stored = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('patient-documents/'.$patient->id, $stored, 'local');

        $document = $patient->documents()->create([
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $stored,
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
            'uploaded_by' => $actor->id,
            'description' => $data['description'] ?? null,
            'is_confidential' => true,
        ]);

        $this->auditLog->record($actor, 'document_uploaded', 'patients', $patient, 'Patient document uploaded.', null, ['document_id' => $document->id, 'title' => $document->title], request());

        return $document;
    }

    public function download(PatientDocument $document, User $actor): string
    {
        $this->auditLog->record($actor, 'document_downloaded', 'patients', $document->patient, 'Patient document downloaded.', null, ['document_id' => $document->id], request());

        return Storage::disk($document->storage_disk)->path($document->storage_path);
    }
}
