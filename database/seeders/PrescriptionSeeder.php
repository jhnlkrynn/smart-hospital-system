<?php

namespace Database\Seeders;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Medication;
use App\Models\Prescription;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $consultation = Consultation::query()->with('patient')->first();
        $medication = Medication::first();
        if (! $consultation || ! $medication) {
            return;
        }

        $prescription = Prescription::firstOrCreate(['consultation_id' => $consultation->id], [
            'prescription_number' => app(ReferenceNumberService::class)->prescriptionNumber(),
            'appointment_id' => $consultation->appointment_id,
            'patient_id' => $consultation->patient_id,
            'doctor_employee_id' => $consultation->doctor_employee_id,
            'department_id' => $consultation->department_id,
            'status' => PrescriptionStatus::Finalized,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'clinical_notes' => 'Demo outpatient prescription.',
            'patient_instructions' => 'Take as directed by your physician.',
            'finalized_at' => now(),
        ]);

        $prescription->items()->firstOrCreate(['medication_id' => $medication->id], [
            'medication_number_snapshot' => $medication->medication_number,
            'generic_name_snapshot' => $medication->generic_name,
            'brand_name_snapshot' => $medication->brand_name,
            'dosage_form_snapshot' => $medication->dosageForm?->name,
            'strength_snapshot' => trim((string) $medication->strength_value.' '.($medication->strengthUnit?->symbol ?? '')),
            'quantity' => 10,
            'instructions' => 'One tablet twice daily after meals.',
        ]);
    }
}
