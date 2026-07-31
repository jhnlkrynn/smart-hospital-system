<?php

namespace Database\Seeders;

use App\Models\MedicationCategory;
use Illuminate\Database\Seeder;

class MedicationCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['ANALGESIC', 'Analgesics'], ['ANTIBIOTIC', 'Antibiotics'], ['ANTIHIST', 'Antihistamines'], ['GI', 'Gastrointestinal']] as [$code, $name]) {
            MedicationCategory::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
