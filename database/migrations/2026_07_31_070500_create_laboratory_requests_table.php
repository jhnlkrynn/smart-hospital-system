<?php

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('consultation_id')->constrained('consultations')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('requesting_doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('priority', 30)->default(LaboratoryPriority::Routine->value)->index();
            $table->string('status', 40)->default(LaboratoryRequestStatus::Requested->value)->index();
            $table->text('clinical_information')->nullable();
            $table->text('provisional_diagnosis')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('consultation_id');
            $table->index(['patient_id', 'status'], 'lab_requests_patient_status_idx');
            $table->index(['requesting_doctor_employee_id', 'status'], 'lab_requests_doctor_status_idx');
            $table->index(['requested_at', 'status'], 'lab_requests_requested_status_idx');
            $table->index('released_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_requests');
    }
};
