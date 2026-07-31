<?php

namespace Database\Seeders;

use App\Models\LaboratoryReferenceRange;
use App\Models\LaboratoryTest;
use Illuminate\Database\Seeder;

class LaboratoryReferenceRangeSeeder extends Seeder
{
    public function run(): void
    {
        $ranges = [
            'FBS' => [70, 100, 40, 400, 'mg/dL'],
            'RBS' => [70, 140, 40, 400, 'mg/dL'],
            'CREA' => [0.6, 1.3, 0.2, 8.0, 'mg/dL'],
            'BUN' => [7, 20, 2, 100, 'mg/dL'],
            'HGB' => [120, 170, 60, 220, 'g/L'],
        ];

        foreach ($ranges as $code => [$low, $high, $criticalLow, $criticalHigh, $unit]) {
            $test = LaboratoryTest::where('code', $code)->first();
            if ($test) {
                LaboratoryReferenceRange::firstOrCreate(['laboratory_test_id' => $test->id, 'sex' => null, 'minimum_age_days' => null, 'maximum_age_days' => null], ['lower_bound' => $low, 'upper_bound' => $high, 'critical_lower_bound' => $criticalLow, 'critical_upper_bound' => $criticalHigh, 'unit' => $unit, 'text_reference' => 'Demonstration range only; verify hospital-approved reference intervals before clinical use.', 'is_active' => true]);
            }
        }
    }
}
