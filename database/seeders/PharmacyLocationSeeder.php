<?php

namespace Database\Seeders;

use App\Models\PharmacyLocation;
use Illuminate\Database\Seeder;

class PharmacyLocationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['MAIN', 'Main Pharmacy', false], ['ER', 'Emergency Pharmacy', false], ['QUAR', 'Quarantine Shelf', true]] as [$code, $name, $quarantine]) {
            PharmacyLocation::firstOrCreate(['code' => $code], ['name' => $name, 'is_quarantine' => $quarantine, 'is_active' => true]);
        }
    }
}
