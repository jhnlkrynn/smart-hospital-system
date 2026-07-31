<?php

namespace Database\Factories;

use App\Enums\PatientDocumentType;
use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PatientDocument> */
class PatientDocumentFactory extends Factory
{
    protected $model = PatientDocument::class;

    public function definition(): array
    {
        $stored = Str::uuid().'.pdf';

        return [
            'patient_id' => Patient::factory(),
            'document_type' => PatientDocumentType::Identification,
            'title' => 'Sample Document',
            'original_filename' => 'sample.pdf',
            'stored_filename' => $stored,
            'storage_disk' => 'local',
            'storage_path' => 'patient-documents/sample/'.$stored,
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'is_confidential' => true,
        ];
    }
}
