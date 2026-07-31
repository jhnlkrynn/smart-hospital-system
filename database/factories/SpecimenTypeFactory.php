<?php

namespace Database\Factories;

use App\Models\SpecimenType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SpecimenType> */
class SpecimenTypeFactory extends Factory
{
    protected $model = SpecimenType::class;

    public function definition(): array
    {
        return ['code' => fake()->unique()->bothify('SPC###'), 'name' => fake()->unique()->word().' specimen', 'is_active' => true];
    }
}
