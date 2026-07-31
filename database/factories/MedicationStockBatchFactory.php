<?php

namespace Database\Factories;

use App\Enums\StockBatchStatus;
use App\Models\Medication;
use App\Models\MedicationStockBatch;
use App\Models\PharmacyLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationStockBatchFactory extends Factory
{
    protected $model = MedicationStockBatch::class;

    public function definition(): array
    {
        return ['medication_id' => Medication::factory(), 'pharmacy_location_id' => PharmacyLocation::factory(), 'lot_number' => strtoupper(fake()->unique()->bothify('LOT###')), 'expiration_date' => now()->addYear(), 'quantity_on_hand' => 100, 'quantity_reserved' => 0, 'status' => StockBatchStatus::Available];
    }
}
