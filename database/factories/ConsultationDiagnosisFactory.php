<?php

namespace Database\Factories;

use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use App\Models\Consultation;
use App\Models\ConsultationDiagnosis;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConsultationDiagnosis> */
class ConsultationDiagnosisFactory extends Factory
{
    protected $model = ConsultationDiagnosis::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'diagnosis_name_snapshot' => fake()->words(3, true),
            'diagnosis_code_snapshot' => fake()->bothify('DX-####'),
            'diagnosis_type' => DiagnosisType::Primary,
            'diagnosis_status' => DiagnosisStatus::Active,
            'is_patient_visible' => true,
            'sync_to_problem_list' => true,
        ];
    }
}
