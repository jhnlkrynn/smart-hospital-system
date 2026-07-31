<?php

use App\Enums\MedicalCertificateStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('status', 30)->default(MedicalCertificateStatus::Draft->value)->index();
            $table->string('purpose');
            $table->text('clinical_summary')->nullable();
            $table->text('recommendation')->nullable();
            $table->date('rest_from')->nullable();
            $table->date('rest_until')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['patient_id', 'status'], 'medical_certificates_patient_status_idx');
            $table->index(['doctor_employee_id', 'status'], 'medical_certificates_doctor_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_certificates');
    }
};
