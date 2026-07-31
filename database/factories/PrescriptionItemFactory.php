<?php

namespace Database\Factories;

use App\Enums\PrescriptionItemStatus;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        $medication = Medication::factory()->create();

        return [
            'prescription_id' => Prescription::factory(),
            'medication_id' => $medication->id,
            'medication_number_snapshot' => $medication->medication_number,
            'generic_name_snapshot' => $medication->generic_name,
            'quantity' => 10,
            'status' => PrescriptionItemStatus::Active,
        ];
    }
}
