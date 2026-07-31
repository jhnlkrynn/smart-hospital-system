<?php

namespace Database\Factories;

use App\Models\MedicationStockBatch;
use App\Models\PharmacyStockReservation;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyStockReservationFactory extends Factory
{
    protected $model = PharmacyStockReservation::class;

    public function definition(): array
    {
        $item = PrescriptionItem::factory()->create();
        $batch = MedicationStockBatch::factory()->create(['medication_id' => $item->medication_id]);

        return ['prescription_id' => $item->prescription_id, 'prescription_item_id' => $item->id, 'medication_id' => $item->medication_id, 'medication_stock_batch_id' => $batch->id, 'quantity_reserved' => 5, 'reserved_at' => now()];
    }
}
