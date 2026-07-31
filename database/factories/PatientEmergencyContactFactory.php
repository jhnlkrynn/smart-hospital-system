<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientEmergencyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientEmergencyContact> */
class PatientEmergencyContactFactory extends Factory
{
    protected $model = PatientEmergencyContact::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['Parent', 'Sibling', 'Spouse', 'Child']),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'is_primary' => false,
        ];
    }
}
