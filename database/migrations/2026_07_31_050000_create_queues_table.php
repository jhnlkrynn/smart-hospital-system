<?php

use App\Enums\QueuePriority;
use App\Enums\QueueStatus;
use App\Enums\VisitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table): void {
            $table->id();
            $table->string('queue_number')->unique();
            $table->foreignId('appointment_id')->nullable()->unique()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->date('queue_date')->index();
            $table->string('status', 30)->default(QueueStatus::Waiting->value)->index();
            $table->string('priority', 30)->default(QueuePriority::Routine->value)->index();
            $table->string('visit_type', 30)->default(VisitType::Appointment->value)->index();
            $table->boolean('is_emergency')->default(false);
            $table->boolean('is_senior_citizen')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_pregnant')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('triage_started_at')->nullable();
            $table->timestamp('triage_completed_at')->nullable();
            $table->timestamp('doctor_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->string('current_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['department_id', 'queue_date', 'status'], 'queues_department_date_status_idx');
            $table->index(['doctor_employee_id', 'queue_date', 'status'], 'queues_doctor_date_status_idx');
            $table->index(['patient_id', 'queue_date'], 'queues_patient_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
