<?php

namespace Database\Factories;

use App\Models\MedicationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationUnitFactory extends Factory
{
    protected $model = MedicationUnit::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('UNIT###')), 'name' => fake()->unique()->word(), 'symbol' => fake()->lexify('??'), 'unit_type' => 'quantity', 'is_active' => true];
    }
}
