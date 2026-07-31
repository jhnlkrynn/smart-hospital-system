<?php

namespace Database\Seeders;

use App\Models\MedicationUnit;
use Illuminate\Database\Seeder;

class MedicationUnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['MG', 'Milligram', 'mg', 'dose'], ['ML', 'Milliliter', 'mL', 'volume'], ['TAB', 'Tablet', 'tab', 'quantity'], ['CAP', 'Capsule', 'cap', 'quantity'], ['BOTTLE', 'Bottle', 'bottle', 'quantity']] as [$code, $name, $symbol, $type]) {
            MedicationUnit::firstOrCreate(['code' => $code], ['name' => $name, 'symbol' => $symbol, 'unit_type' => $type, 'is_active' => true]);
        }
    }
}
