<?php

namespace Database\Factories;

use App\Models\MedicationRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationRouteFactory extends Factory
{
    protected $model = MedicationRoute::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('RTE###')), 'name' => fake()->unique()->word(), 'is_active' => true];
    }
}
