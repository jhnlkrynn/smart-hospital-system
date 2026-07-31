<?php

namespace Database\Factories;

use App\Models\MedicationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationCategoryFactory extends Factory
{
    protected $model = MedicationCategory::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('CAT###')), 'name' => fake()->unique()->word(), 'is_active' => true];
    }
}
