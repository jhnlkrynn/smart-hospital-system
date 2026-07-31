<?php

namespace Database\Factories;

use App\Models\LaboratoryReferenceRange;
use App\Models\LaboratoryTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryReferenceRange> */
class LaboratoryReferenceRangeFactory extends Factory
{
    protected $model = LaboratoryReferenceRange::class;

    public function definition(): array
    {
        return ['laboratory_test_id' => LaboratoryTest::factory(), 'lower_bound' => 70, 'upper_bound' => 100, 'critical_lower_bound' => 40, 'critical_upper_bound' => 400, 'unit' => 'mg/dL', 'is_active' => true];
    }
}
