<?php

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        $consultation = Consultation::factory()->create();

        return [
            'prescription_number' => strtoupper(fake()->unique()->bothify('RX-20260731-#####')),
            'consultation_id' => $consultation->id,
            'appointment_id' => $consultation->appointment_id,
            'patient_id' => $consultation->patient_id,
            'doctor_employee_id' => $consultation->doctor_employee_id,
            'department_id' => $consultation->department_id,
            'status' => PrescriptionStatus::Draft,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
        ];
    }
}
