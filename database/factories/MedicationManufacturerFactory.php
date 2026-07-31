<?php

namespace Database\Factories;

use App\Models\MedicationManufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationManufacturerFactory extends Factory
{
    protected $model = MedicationManufacturer::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('MFR###')), 'name' => fake()->unique()->company(), 'is_active' => true];
    }
}
