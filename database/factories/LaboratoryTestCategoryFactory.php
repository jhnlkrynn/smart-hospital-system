<?php

namespace Database\Factories;

use App\Models\LaboratoryTestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryTestCategory> */
class LaboratoryTestCategoryFactory extends Factory
{
    protected $model = LaboratoryTestCategory::class;

    public function definition(): array
    {
        return ['code' => fake()->unique()->bothify('CAT###'), 'name' => fake()->unique()->words(2, true), 'display_order' => fake()->numberBetween(1, 20), 'is_active' => true];
    }
}
