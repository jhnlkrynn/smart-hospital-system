<?php

namespace Database\Factories;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Department> */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'location' => fake()->city(),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'status' => DepartmentStatus::Active,
        ];
    }
}
