<?php

namespace Database\Factories;

use App\Models\MedicationFrequency;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFrequencyFactory extends Factory
{
    protected $model = MedicationFrequency::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('FREQ###')), 'name' => fake()->unique()->words(2, true), 'times_per_day' => 2, 'is_active' => true];
    }
}
