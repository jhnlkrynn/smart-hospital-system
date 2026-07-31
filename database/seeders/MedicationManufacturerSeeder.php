<?php

namespace Database\Seeders;

use App\Models\MedicationManufacturer;
use Illuminate\Database\Seeder;

class MedicationManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['GENPHARMA', 'Generic Pharma Inc.'], ['MEDSUPPLY', 'Metro Medical Supply']] as [$code, $name]) {
            MedicationManufacturer::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
