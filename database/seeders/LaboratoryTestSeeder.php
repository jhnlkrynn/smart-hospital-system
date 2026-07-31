<?php

namespace Database\Seeders;

use App\Enums\LaboratoryResultType;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestCategory;
use App\Models\SpecimenType;
use Illuminate\Database\Seeder;

class LaboratoryTestSeeder extends Seeder
{
    public function run(): void
    {
        $blood = SpecimenType::where('code', 'BLD')->first();
        $serum = SpecimenType::where('code', 'SER')->first();
        $urine = SpecimenType::where('code', 'URN')->first();
        $hem = LaboratoryTestCategory::where('code', 'HEM')->first();
        $chem = LaboratoryTestCategory::where('code', 'CHEM')->first();
        $ua = LaboratoryTestCategory::where('code', 'UA')->first();

        $tests = [
            ['HGB', 'Hemoglobin', $hem?->id, $blood?->id, 'g/L'],
            ['HCT', 'Hematocrit', $hem?->id, $blood?->id, '%'],
            ['WBC', 'WBC Count', $hem?->id, $blood?->id, '10^9/L'],
            ['PLT', 'Platelet Count', $hem?->id, $blood?->id, '10^9/L'],
            ['FBS', 'Fasting Blood Sugar', $chem?->id, $serum?->id, 'mg/dL'],
            ['RBS', 'Random Blood Sugar', $chem?->id, $serum?->id, 'mg/dL'],
            ['CREA', 'Creatinine', $chem?->id, $serum?->id, 'mg/dL'],
            ['BUN', 'Blood Urea Nitrogen', $chem?->id, $serum?->id, 'mg/dL'],
            ['URINALYSIS', 'Urinalysis', $ua?->id, $urine?->id, null],
        ];

        foreach ($tests as [$code, $name, $categoryId, $specimenId, $unit]) {
            LaboratoryTest::firstOrCreate(['code' => $code], ['name' => $name, 'laboratory_test_category_id' => $categoryId, 'specimen_type_id' => $specimenId, 'result_type' => LaboratoryResultType::Numeric, 'default_unit' => $unit, 'is_active' => true]);
        }

        $cbc = LaboratoryTest::firstOrCreate(['code' => 'CBC'], ['name' => 'Complete Blood Count', 'laboratory_test_category_id' => $hem?->id, 'specimen_type_id' => $blood?->id, 'result_type' => LaboratoryResultType::Structured, 'is_panel' => true, 'is_active' => true]);
        $components = LaboratoryTest::whereIn('code', ['HGB', 'HCT', 'WBC', 'PLT'])->pluck('id')->all();
        $cbc->components()->sync(collect($components)->mapWithKeys(fn ($id, $index) => [$id => ['display_order' => $index + 1, 'is_required' => true]])->all());
    }
}
