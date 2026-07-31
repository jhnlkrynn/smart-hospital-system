<?php

namespace Database\Factories;

use App\Enums\InventoryTransactionType;
use App\Models\MedicationStockBatch;
use App\Models\PharmacyInventoryTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyInventoryTransactionFactory extends Factory
{
    protected $model = PharmacyInventoryTransaction::class;

    public function definition(): array
    {
        $batch = MedicationStockBatch::factory()->create();

        return ['transaction_number' => strtoupper(fake()->unique()->bothify('INVTXN-20260731-######')), 'medication_id' => $batch->medication_id, 'medication_stock_batch_id' => $batch->id, 'pharmacy_location_id' => $batch->pharmacy_location_id, 'transaction_type' => InventoryTransactionType::OpeningBalance, 'quantity' => 10, 'quantity_before' => 0, 'quantity_after' => 10, 'occurred_at' => now()];
    }
}
