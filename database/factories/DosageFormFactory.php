<?php

namespace Database\Factories;

use App\Models\DosageForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class DosageFormFactory extends Factory
{
    protected $model = DosageForm::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('FORM###')), 'name' => fake()->unique()->word(), 'is_active' => true];
    }
}
