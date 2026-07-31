<?php

namespace Database\Factories;

use App\Enums\LaboratoryResultType;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestCategory;
use App\Models\SpecimenType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryTest> */
class LaboratoryTestFactory extends Factory
{
    protected $model = LaboratoryTest::class;

    public function definition(): array
    {
        return [
            'laboratory_test_category_id' => LaboratoryTestCategory::factory(),
            'code' => fake()->unique()->bothify('LAB###'),
            'name' => fake()->unique()->words(3, true),
            'result_type' => LaboratoryResultType::Numeric,
            'default_unit' => 'mg/dL',
            'specimen_type_id' => SpecimenType::factory(),
            'requires_verification' => true,
            'is_panel' => false,
            'is_active' => true,
        ];
    }
}
