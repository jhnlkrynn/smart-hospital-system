<?php

namespace Database\Seeders;

use App\Enums\ConsultationStatus;
use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use App\Models\Consultation;
use App\Models\ConsultationDiagnosis;
use App\Models\DiagnosisCatalog;
use App\Models\Employee;
use App\Models\MedicalCertificate;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Employee::query()->whereHas('user.roles', fn ($query) => $query->where('name', 'doctor'))->first();
        $patient = Patient::query()->first();
        $catalog = DiagnosisCatalog::query()->first();

        if (! $doctor || ! $patient || ! $catalog) {
            return;
        }

        $consultation = Consultation::firstOrCreate(
            ['consultation_number' => 'CON-'.now('Asia/Manila')->format('Y').'-000001'],
            [
                'patient_id' => $patient->id,
                'doctor_employee_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'status' => ConsultationStatus::Completed,
                'started_at' => now()->subHour(),
                'completed_at' => now()->subMinutes(25),
                'subjective_notes' => 'Patient reports cough and nasal congestion.',
                'objective_notes' => 'Stable vital signs. No respiratory distress.',
                'clinical_impression' => 'Upper respiratory tract infection.',
                'treatment_plan' => 'Supportive care, hydration, and rest.',
                'follow_up_instructions' => 'Return if fever persists or breathing difficulty develops.',
                'patient_summary' => 'You were assessed for mild respiratory symptoms and advised supportive care.',
                'is_patient_visible' => true,
            ],
        );

        ConsultationDiagnosis::firstOrCreate(
            ['consultation_id' => $consultation->id, 'diagnosis_code_snapshot' => $catalog->code],
            [
                'diagnosis_catalog_id' => $catalog->id,
                'diagnosis_name_snapshot' => $catalog->name,
                'diagnosis_type' => DiagnosisType::Primary,
                'diagnosis_status' => DiagnosisStatus::Active,
                'is_patient_visible' => true,
                'sync_to_problem_list' => true,
            ],
        );

        MedicalCertificate::factory()->issued()->create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'doctor_employee_id' => $doctor->id,
        ]);
    }
}
