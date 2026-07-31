<?php

namespace Database\Factories;

use App\Models\DiagnosisCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DiagnosisCatalog> */
class DiagnosisCatalogFactory extends Factory
{
    protected $model = DiagnosisCatalog::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('DX-####'),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['General Medicine', 'Respiratory', 'Cardiology', 'Infectious Disease']),
            'is_active' => true,
            'is_patient_visible_default' => true,
        ];
    }
}
