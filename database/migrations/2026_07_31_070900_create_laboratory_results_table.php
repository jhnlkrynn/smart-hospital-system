<?php

use App\Enums\LaboratoryAbnormalFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_request_item_id')->unique()->constrained('laboratory_request_items')->cascadeOnDelete();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('laboratory_test_id')->constrained('laboratory_tests')->restrictOnDelete();
            $table->string('result_type', 30);
            $table->decimal('numeric_value', 14, 4)->nullable();
            $table->text('text_value')->nullable();
            $table->string('qualitative_value')->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->json('structured_value')->nullable();
            $table->string('unit', 50)->nullable();
            $table->foreignId('reference_range_id')->nullable()->constrained('laboratory_reference_ranges')->nullOnDelete();
            $table->decimal('reference_lower_bound', 12, 4)->nullable();
            $table->decimal('reference_upper_bound', 12, 4)->nullable();
            $table->decimal('critical_lower_bound', 12, 4)->nullable();
            $table->decimal('critical_upper_bound', 12, 4)->nullable();
            $table->string('text_reference')->nullable();
            $table->string('abnormal_flag', 40)->default(LaboratoryAbnormalFlag::NotApplicable->value)->index();
            $table->boolean('is_critical')->default(false)->index();
            $table->text('flag_override_reason')->nullable();
            $table->text('technical_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('entered_at')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('is_patient_visible')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['laboratory_request_id', 'released_at'], 'lab_results_request_released_idx');
            $table->index(['patient_id', 'released_at'], 'lab_results_patient_released_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_results');
    }
};
