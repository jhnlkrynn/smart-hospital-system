<?php

namespace Database\Factories;

use App\Enums\MedicalCertificateStatus;
use App\Models\Consultation;
use App\Models\Employee;
use App\Models\MedicalCertificate;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MedicalCertificate> */
class MedicalCertificateFactory extends Factory
{
    protected $model = MedicalCertificate::class;

    public function definition(): array
    {
        return [
            'certificate_number' => 'MEDCERT-'.now('Asia/Manila')->format('Y').'-'.fake()->unique()->numberBetween(100000, 999999),
            'consultation_id' => Consultation::factory(),
            'patient_id' => Patient::factory(),
            'doctor_employee_id' => Employee::factory(),
            'status' => MedicalCertificateStatus::Draft,
            'purpose' => 'Work absence',
            'clinical_summary' => fake()->sentence(),
            'recommendation' => fake()->sentence(),
        ];
    }

    public function issued(): self
    {
        return $this->state(fn () => [
            'status' => MedicalCertificateStatus::Issued,
            'issued_at' => now(),
        ]);
    }
}
