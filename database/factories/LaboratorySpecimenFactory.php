<?php

namespace Database\Factories;

use App\Enums\SpecimenStatus;
use App\Models\LaboratoryRequest;
use App\Models\LaboratorySpecimen;
use App\Models\SpecimenType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<LaboratorySpecimen> */
class LaboratorySpecimenFactory extends Factory
{
    protected $model = LaboratorySpecimen::class;

    public function definition(): array
    {
        $request = LaboratoryRequest::factory()->create();

        return [
            'accession_number' => 'ACC-'.now('Asia/Manila')->format('Ymd').'-'.fake()->unique()->numberBetween(10000, 99999),
            'laboratory_request_id' => $request->id,
            'patient_id' => $request->patient_id,
            'specimen_type_id' => SpecimenType::factory(),
            'status' => SpecimenStatus::Collected,
            'collected_at' => now(),
            'barcode_value' => 'LAB-'.Str::uuid(),
        ];
    }
}
