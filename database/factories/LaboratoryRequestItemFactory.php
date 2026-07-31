<?php

namespace Database\Factories;

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryTestItemStatus;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryRequestItem> */
class LaboratoryRequestItemFactory extends Factory
{
    protected $model = LaboratoryRequestItem::class;

    public function definition(): array
    {
        $test = LaboratoryTest::factory()->create();

        return [
            'laboratory_request_id' => LaboratoryRequest::factory(),
            'laboratory_test_id' => $test->id,
            'test_code_snapshot' => $test->code,
            'test_name_snapshot' => $test->name,
            'result_type_snapshot' => $test->result_type,
            'unit_snapshot' => $test->default_unit,
            'specimen_type_id' => $test->specimen_type_id,
            'priority' => LaboratoryPriority::Routine,
            'status' => LaboratoryTestItemStatus::Pending,
        ];
    }
}
