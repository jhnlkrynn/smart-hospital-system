<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\ConsultationAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConsultationAttachment> */
class ConsultationAttachmentFactory extends Factory
{
    protected $model = ConsultationAttachment::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'title' => fake()->words(2, true),
            'original_filename' => 'clinical-note.pdf',
            'stored_filename' => fake()->uuid().'.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'consultation-attachments/demo.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'is_confidential' => false,
            'is_patient_visible' => false,
        ];
    }
}
