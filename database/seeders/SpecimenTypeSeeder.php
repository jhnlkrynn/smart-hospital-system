<?php

namespace Database\Seeders;

use App\Models\SpecimenType;
use Illuminate\Database\Seeder;

class SpecimenTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['BLD', 'Blood'], ['SER', 'Serum'], ['PLS', 'Plasma'], ['URN', 'Urine'], ['STL', 'Stool'], ['SPT', 'Sputum'], ['SWB', 'Swab'], ['OTH', 'Other']] as [$code, $name]) {
            SpecimenType::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
