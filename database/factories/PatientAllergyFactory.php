<?php

namespace Database\Factories;

use App\Enums\AllergySeverity;
use App\Enums\AllergyType;
use App\Models\Patient;
use App\Models\PatientAllergy;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientAllergy> */
class PatientAllergyFactory extends Factory
{
    protected $model = PatientAllergy::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'allergen' => fake()->randomElement(['Penicillin', 'Peanuts', 'Dust', 'Shellfish']),
            'allergy_type' => fake()->randomElement(AllergyType::cases()),
            'reaction' => fake()->optional()->sentence(3),
            'severity' => fake()->randomElement(AllergySeverity::cases()),
            'is_active' => true,
        ];
    }
}
