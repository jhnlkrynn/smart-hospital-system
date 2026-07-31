<?php

namespace Database\Factories;

use App\Models\AppointmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppointmentType> */
class AppointmentTypeFactory extends Factory
{
    protected $model = AppointmentType::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('TYPE###')),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'default_duration_minutes' => fake()->randomElement([20, 30, 45, 60]),
            'requires_approval' => true,
            'is_active' => true,
        ];
    }
}
