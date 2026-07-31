<?php

namespace Database\Seeders;

use App\Models\LaboratoryTestCategory;
use Illuminate\Database\Seeder;

class LaboratoryTestCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['HEM', 'Hematology'], ['CHEM', 'Clinical Chemistry'], ['UA', 'Urinalysis'], ['SERO', 'Serology'], ['MICRO', 'Microbiology'], ['COAG', 'Coagulation']] as $index => [$code, $name]) {
            LaboratoryTestCategory::firstOrCreate(['code' => $code], ['name' => $name, 'display_order' => $index + 1, 'is_active' => true]);
        }
    }
}
