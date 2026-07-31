<?php

namespace Database\Seeders;

use App\Models\DosageForm;
use Illuminate\Database\Seeder;

class DosageFormSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['TAB', 'Tablet'], ['CAP', 'Capsule'], ['SYR', 'Syrup'], ['INJ', 'Injection']] as [$code, $name]) {
            DosageForm::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
