<?php

namespace Tests\Feature\Doctor;

use App\Enums\AppointmentStatus;
use App\Enums\ConsultationStatus;
use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\ConsultationDiagnosis;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PatientQueue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_starts_consultation_from_assigned_queue(): void
    {
        [$user, $doctor] = $this->doctor();
        $queue = PatientQueue::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id, 'status' => QueueStatus::Triaged]);

        $this->actingAs($user)->post(route('doctor.queues.consultations.start', $queue))->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'queue_entry_id' => $queue->id,
            'doctor_employee_id' => $doctor->id,
            'status' => ConsultationStatus::InProgress->value,
        ]);
        $this->assertDatabaseHas('queues', ['id' => $queue->id, 'status' => QueueStatus::WithDoctor->value]);
    }

    public function test_consultation_start_blocks_duplicate_and_other_doctor(): void
    {
        [$user, $doctor] = $this->doctor();
        [, $otherDoctor] = $this->doctor();
        $own = PatientQueue::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id, 'status' => QueueStatus::Triaged]);
        $other = PatientQueue::factory()->create(['doctor_employee_id' => $otherDoctor->id, 'department_id' => $otherDoctor->department_id, 'status' => QueueStatus::Triaged]);

        $this->actingAs($user)->post(route('doctor.queues.consultations.start', $own))->assertRedirect();
        $this->actingAs($user)->post(route('doctor.queues.consultations.start', $own))->assertSessionHasErrors('queue');
        $this->actingAs($user)->post(route('doctor.queues.consultations.start', $other))->assertSessionHasErrors('queue');
    }

    public function test_completion_updates_consultation_queue_and_appointment(): void
    {
        [$user, $doctor] = $this->doctor();
        $appointment = Appointment::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id, 'status' => AppointmentStatus::CheckedIn]);
        $queue = PatientQueue::factory()->create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_employee_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'status' => QueueStatus::Triaged,
        ]);
        $this->actingAs($user)->post(route('doctor.queues.consultations.start', $queue));
        $consultation = Consultation::query()->where('queue_entry_id', $queue->id)->firstOrFail();
        ConsultationDiagnosis::factory()->create(['consultation_id' => $consultation->id, 'diagnosis_type' => DiagnosisType::Primary, 'diagnosis_status' => DiagnosisStatus::Active]);

        $this->actingAs($user)->post(route('doctor.consultations.complete', $consultation), [
            'clinical_impression' => 'Stable upper respiratory infection.',
            'treatment_plan' => 'Hydration and supportive medicines.',
            'follow_up_instructions' => 'Return in three days if symptoms persist.',
        ])->assertRedirect();

        $this->assertDatabaseHas('consultations', ['id' => $consultation->id, 'status' => ConsultationStatus::Completed->value, 'is_patient_visible' => true]);
        $this->assertDatabaseHas('queues', ['id' => $queue->id, 'status' => QueueStatus::Completed->value]);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => AppointmentStatus::Completed->value]);
    }

    public function test_completed_consultations_are_read_only(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->completed()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);

        $this->actingAs($user)->put(route('doctor.consultations.update', $consultation), ['clinical_impression' => 'Changed'])
            ->assertSessionHasErrors('consultation');
    }

    private function doctor(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $employee = Employee::factory()->create(['user_id' => $user->id, 'department_id' => Department::factory()]);

        return [$user, $employee];
    }
}
