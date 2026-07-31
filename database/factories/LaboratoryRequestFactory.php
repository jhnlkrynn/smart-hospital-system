<?php

namespace Database\Factories;

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LaboratoryRequest> */
class LaboratoryRequestFactory extends Factory
{
    protected $model = LaboratoryRequest::class;

    public function definition(): array
    {
        $consultation = Consultation::factory()->create();

        return [
            'request_number' => 'LAB-'.now('Asia/Manila')->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'consultation_id' => $consultation->id,
            'appointment_id' => $consultation->appointment_id,
            'patient_id' => $consultation->patient_id,
            'requesting_doctor_employee_id' => $consultation->doctor_employee_id,
            'department_id' => $consultation->department_id,
            'priority' => LaboratoryPriority::Routine,
            'status' => LaboratoryRequestStatus::SpecimenPending,
            'requested_at' => now(),
        ];
    }
}
