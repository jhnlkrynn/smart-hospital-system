<?php

namespace Database\Factories;

use App\Models\LaboratoryResult;
use App\Models\LaboratoryResultAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryResultAttachment> */
class LaboratoryResultAttachmentFactory extends Factory
{
    protected $model = LaboratoryResultAttachment::class;

    public function definition(): array
    {
        return ['laboratory_result_id' => LaboratoryResult::factory(), 'title' => 'Result attachment', 'original_filename' => 'result.pdf', 'stored_filename' => fake()->uuid().'.pdf', 'storage_disk' => 'local', 'storage_path' => 'laboratory-result-attachments/demo.pdf', 'mime_type' => 'application/pdf', 'file_size' => 1024];
    }
}
