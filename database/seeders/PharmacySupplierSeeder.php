<?php

namespace Database\Seeders;

use App\Models\PharmacySupplier;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class PharmacySupplierSeeder extends Seeder
{
    public function run(): void
    {
        $numbers = app(ReferenceNumberService::class);
        foreach (['MetroMed Distribution', 'CarePlus Medical Trading'] as $name) {
            PharmacySupplier::firstOrCreate(['name' => $name], ['supplier_number' => $numbers->pharmacySupplierNumber(), 'is_active' => true]);
        }
    }
}
