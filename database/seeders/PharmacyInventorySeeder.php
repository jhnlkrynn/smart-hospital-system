<?php

namespace Database\Seeders;

use App\Enums\InventoryTransactionType;
use App\Enums\StockBatchStatus;
use App\Models\Medication;
use App\Models\MedicationStockBatch;
use App\Models\PharmacyInventoryTransaction;
use App\Models\PharmacyLocation;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class PharmacyInventorySeeder extends Seeder
{
    public function run(): void
    {
        $numbers = app(ReferenceNumberService::class);
        $location = PharmacyLocation::where('code', 'MAIN')->first();
        if (! $location) {
            return;
        }

        foreach (Medication::query()->take(4)->get() as $medication) {
            $batch = MedicationStockBatch::firstOrCreate([
                'medication_id' => $medication->id,
                'pharmacy_location_id' => $location->id,
                'lot_number' => 'OPEN-'.$medication->id,
            ], [
                'expiration_date' => now()->addYear()->toDateString(),
                'quantity_on_hand' => 100,
                'quantity_reserved' => 0,
                'status' => StockBatchStatus::Available,
            ]);

            PharmacyInventoryTransaction::firstOrCreate([
                'medication_stock_batch_id' => $batch->id,
                'transaction_type' => InventoryTransactionType::OpeningBalance->value,
            ], [
                'transaction_number' => $numbers->inventoryTransactionNumber(),
                'medication_id' => $medication->id,
                'pharmacy_location_id' => $location->id,
                'quantity' => 100,
                'quantity_before' => 0,
                'quantity_after' => 100,
                'occurred_at' => now(),
            ]);
        }
    }
}
