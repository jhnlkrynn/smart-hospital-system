<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_result_acknowledgments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_result_id')->constrained('laboratory_results')->cascadeOnDelete();
            $table->foreignId('doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('acknowledged_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['laboratory_result_id', 'doctor_employee_id'], 'lab_result_ack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_result_acknowledgments');
    }
};
