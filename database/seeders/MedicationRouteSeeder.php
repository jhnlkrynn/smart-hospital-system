<?php

namespace Database\Seeders;

use App\Models\MedicationRoute;
use Illuminate\Database\Seeder;

class MedicationRouteSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['PO', 'Oral'], ['IV', 'Intravenous'], ['IM', 'Intramuscular'], ['TOP', 'Topical']] as [$code, $name]) {
            MedicationRoute::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
