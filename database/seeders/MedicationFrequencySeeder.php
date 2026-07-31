<?php

namespace Database\Seeders;

use App\Models\MedicationFrequency;
use Illuminate\Database\Seeder;

class MedicationFrequencySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['OD', 'Once daily', 1], ['BID', 'Twice daily', 2], ['TID', 'Three times daily', 3], ['QID', 'Four times daily', 4], ['PRN', 'As needed', null]] as [$code, $name, $times]) {
            MedicationFrequency::firstOrCreate(['code' => $code], ['name' => $name, 'abbreviation' => $code, 'times_per_day' => $times, 'is_active' => true]);
        }
    }
}
