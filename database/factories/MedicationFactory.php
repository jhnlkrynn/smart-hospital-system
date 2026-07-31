<?php

namespace Database\Factories;

use App\Enums\MedicationStatus;
use App\Models\DosageForm;
use App\Models\Medication;
use App\Models\MedicationCategory;
use App\Models\MedicationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'medication_number' => strtoupper(fake()->unique()->bothify('MED-2026-######')),
            'generic_name' => fake()->randomElement(['Paracetamol', 'Amoxicillin', 'Cetirizine', 'Ibuprofen']),
            'brand_name' => fake()->optional()->company(),
            'medication_category_id' => MedicationCategory::factory(),
            'dosage_form_id' => DosageForm::factory(),
            'strength_value' => 500,
            'strength_unit_id' => MedicationUnit::factory(),
            'status' => MedicationStatus::Active,
            'formulary_status' => 'formulary',
            'requires_prescription' => true,
            'is_active' => true,
        ];
    }
}
