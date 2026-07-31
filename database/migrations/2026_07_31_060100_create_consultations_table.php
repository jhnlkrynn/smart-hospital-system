<?php

use App\Enums\ConsultationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table): void {
            $table->id();
            $table->string('consultation_number')->unique();
            $table->foreignId('queue_entry_id')->nullable()->unique()->constrained('queues')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('status', 30)->default(ConsultationStatus::Waiting->value)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();
            $table->string('chief_complaint')->nullable();
            $table->text('history_of_present_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('social_history')->nullable();
            $table->text('review_of_systems')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('subjective_notes')->nullable();
            $table->text('objective_notes')->nullable();
            $table->text('assessment')->nullable();
            $table->text('clinical_impression')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('care_instructions')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('internal_doctor_notes')->nullable();
            $table->text('patient_summary')->nullable();
            $table->boolean('is_patient_visible')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['patient_id', 'completed_at'], 'consultations_patient_completed_idx');
            $table->index(['doctor_employee_id', 'status'], 'consultations_doctor_status_idx');
            $table->index(['department_id', 'status'], 'consultations_department_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
