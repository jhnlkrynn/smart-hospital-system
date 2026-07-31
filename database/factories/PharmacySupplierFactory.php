<?php

namespace Database\Factories;

use App\Models\PharmacySupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacySupplierFactory extends Factory
{
    protected $model = PharmacySupplier::class;

    public function definition(): array
    {
        return ['supplier_number' => strtoupper(fake()->unique()->bothify('SUP-####')), 'name' => fake()->company(), 'email' => fake()->safeEmail(), 'phone' => fake()->phoneNumber(), 'is_active' => true];
    }
}
