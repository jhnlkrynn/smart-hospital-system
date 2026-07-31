<?php

namespace Database\Factories;

use App\Models\PharmacyLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyLocationFactory extends Factory
{
    protected $model = PharmacyLocation::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('LOC###')), 'name' => fake()->unique()->words(2, true), 'is_quarantine' => false, 'is_active' => true];
    }
}
