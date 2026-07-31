<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySupplier;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class PharmacyPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = PharmacySupplier::first();
        $medication = Medication::first();
        if (! $supplier || ! $medication) {
            return;
        }

        $purchase = PharmacyPurchase::firstOrCreate(['purchase_number' => 'PO-'.now()->format('Ymd').'-DEMO1'], [
            'pharmacy_supplier_id' => $supplier->id,
            'status' => 'ordered',
            'order_date' => now()->toDateString(),
        ]);

        $purchase->items()->firstOrCreate(['medication_id' => $medication->id], ['ordered_quantity' => 50, 'unit_cost' => 2.5]);
    }
}
