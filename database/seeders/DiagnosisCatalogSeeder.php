<?php

namespace Database\Seeders;

use App\Models\DiagnosisCatalog;
use Illuminate\Database\Seeder;

class DiagnosisCatalogSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['code' => 'J00', 'name' => 'Acute nasopharyngitis', 'category' => 'Respiratory'],
            ['code' => 'I10', 'name' => 'Essential hypertension', 'category' => 'Cardiology'],
            ['code' => 'E11.9', 'name' => 'Type 2 diabetes mellitus without complications', 'category' => 'Endocrinology'],
            ['code' => 'K29.7', 'name' => 'Gastritis, unspecified', 'category' => 'Gastroenterology'],
            ['code' => 'N39.0', 'name' => 'Urinary tract infection, site not specified', 'category' => 'Infectious Disease'],
        ])->each(fn (array $item) => DiagnosisCatalog::firstOrCreate(
            ['code' => $item['code']],
            $item + ['is_active' => true, 'is_patient_visible_default' => true],
        ));
    }
}
