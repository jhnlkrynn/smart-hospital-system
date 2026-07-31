<?php

use App\Enums\DiagnosisStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_problems', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('diagnosis_catalog_id')->nullable()->constrained('diagnosis_catalog')->nullOnDelete();
            $table->foreignId('source_consultation_diagnosis_id')->nullable()->constrained('consultation_diagnoses')->nullOnDelete();
            $table->string('problem_name');
            $table->string('problem_code', 50)->nullable();
            $table->string('status', 30)->default(DiagnosisStatus::Active->value)->index();
            $table->date('onset_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_chronic')->default(false)->index();
            $table->boolean('is_patient_visible')->default(true);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['patient_id', 'status'], 'patient_problems_patient_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_problems');
    }
};
