<?php

namespace Database\Factories;

use App\Enums\LaboratoryAbnormalFlag;
use App\Enums\LaboratoryResultType;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryResult> */
class LaboratoryResultFactory extends Factory
{
    protected $model = LaboratoryResult::class;

    public function definition(): array
    {
        $item = LaboratoryRequestItem::factory()->create();

        return [
            'laboratory_request_item_id' => $item->id,
            'laboratory_request_id' => $item->laboratory_request_id,
            'patient_id' => $item->laboratoryRequest->patient_id,
            'laboratory_test_id' => $item->laboratory_test_id,
            'result_type' => LaboratoryResultType::Numeric,
            'numeric_value' => 90,
            'unit' => $item->unit_snapshot,
            'abnormal_flag' => LaboratoryAbnormalFlag::Normal,
            'entered_at' => now(),
        ];
    }

    public function released(): self
    {
        return $this->state(fn () => ['verified_at' => now(), 'released_at' => now(), 'is_patient_visible' => true]);
    }
}
