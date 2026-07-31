<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_diagnoses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('diagnosis_catalog_id')->nullable()->constrained('diagnosis_catalog')->nullOnDelete();
            $table->string('diagnosis_name_snapshot');
            $table->string('diagnosis_code_snapshot', 50)->nullable();
            $table->string('diagnosis_type', 30)->index();
            $table->string('diagnosis_status', 30)->index();
            $table->text('clinical_notes')->nullable();
            $table->date('onset_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->boolean('is_patient_visible')->default(true)->index();
            $table->boolean('sync_to_problem_list')->default(true);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['consultation_id', 'diagnosis_type'], 'consultation_diagnoses_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_diagnoses');
    }
};
