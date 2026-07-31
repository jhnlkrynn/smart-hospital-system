<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_critical_result_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_result_id')->constrained('laboratory_results')->cascadeOnDelete();
            $table->foreignId('laboratory_request_id')->constrained('laboratory_requests')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('identified_at');
            $table->foreignId('identified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('communicated_at')->nullable();
            $table->foreignId('communicated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('communication_method')->nullable();
            $table->string('communicated_to')->nullable();
            $table->text('communication_notes')->nullable();
            $table->timestamps();
            $table->index(['doctor_employee_id', 'communicated_at'], 'lab_critical_doctor_comm_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_critical_result_logs');
    }
};
