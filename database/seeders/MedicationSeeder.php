<?php

namespace Database\Seeders;

use App\Enums\MedicationStatus;
use App\Models\DosageForm;
use App\Models\Medication;
use App\Models\MedicationCategory;
use App\Models\MedicationFrequency;
use App\Models\MedicationManufacturer;
use App\Models\MedicationRoute;
use App\Models\MedicationUnit;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        $numbers = app(ReferenceNumberService::class);
        $mg = MedicationUnit::where('code', 'MG')->first();
        $tablet = DosageForm::where('code', 'TAB')->first();
        $oral = MedicationRoute::where('code', 'PO')->first();
        $bid = MedicationFrequency::where('code', 'BID')->first();
        $manufacturer = MedicationManufacturer::first();

        foreach ([['Paracetamol', 'Analgesics', 500], ['Amoxicillin', 'Antibiotics', 500], ['Cetirizine', 'Antihistamines', 10], ['Ibuprofen', 'Analgesics', 200]] as [$generic, $category, $strength]) {
            Medication::firstOrCreate(['generic_name' => $generic, 'strength_value' => $strength], [
                'medication_number' => $numbers->medicationNumber(),
                'medication_category_id' => MedicationCategory::where('name', $category)->first()?->id,
                'dosage_form_id' => $tablet?->id,
                'strength_unit_id' => $mg?->id,
                'manufacturer_id' => $manufacturer?->id,
                'status' => MedicationStatus::Active,
                'formulary_status' => 'formulary',
                'requires_prescription' => true,
                'default_route_id' => $oral?->id,
                'default_frequency_id' => $bid?->id,
                'is_active' => true,
            ]);
        }
    }
}
