<?php

use App\Enums\TriageAcuity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('queue_id')->unique()->constrained('queues')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('nurse_id')->constrained('users')->restrictOnDelete();
            $table->string('chief_complaint');
            $table->unsignedTinyInteger('pain_scale')->default(0);
            $table->boolean('pregnancy_flag')->default(false);
            $table->unsignedTinyInteger('fall_risk_score')->default(0);
            $table->string('fall_risk_level', 30)->default('low');
            $table->text('fall_risk_notes')->nullable();
            $table->string('acuity', 30)->default(TriageAcuity::Routine->value)->index();
            $table->boolean('allergies_reviewed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage_records');
    }
};
