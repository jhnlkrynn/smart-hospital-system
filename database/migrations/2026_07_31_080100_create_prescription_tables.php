<?php

use App\Enums\PrescriptionItemStatus;
use App\Enums\PrescriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('consultation_id')->constrained('consultations')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('status', 40)->default(PrescriptionStatus::Draft->value)->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->text('patient_instructions')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('replaces_prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->foreignId('replaced_by_prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['patient_id', 'status'], 'prescriptions_patient_status_idx');
            $table->index(['doctor_employee_id', 'status'], 'prescriptions_doctor_status_idx');
        });

        Schema::create('prescription_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->string('medication_number_snapshot');
            $table->string('generic_name_snapshot');
            $table->string('brand_name_snapshot')->nullable();
            $table->string('dosage_form_snapshot')->nullable();
            $table->string('strength_snapshot')->nullable();
            $table->decimal('dose_quantity', 12, 3)->nullable();
            $table->foreignId('dose_unit_id')->nullable()->constrained('medication_units')->nullOnDelete();
            $table->string('dose_unit_snapshot')->nullable();
            $table->foreignId('route_id')->nullable()->constrained('medication_routes')->nullOnDelete();
            $table->string('route_snapshot')->nullable();
            $table->foreignId('frequency_id')->nullable()->constrained('medication_frequencies')->nullOnDelete();
            $table->string('frequency_snapshot')->nullable();
            $table->unsignedInteger('duration_value')->nullable();
            $table->string('duration_unit', 30)->nullable();
            $table->decimal('quantity', 12, 3);
            $table->foreignId('quantity_unit_id')->nullable()->constrained('medication_units')->nullOnDelete();
            $table->string('quantity_unit_snapshot')->nullable();
            $table->text('instructions')->nullable();
            $table->text('pharmacy_notes')->nullable();
            $table->string('status', 40)->default(PrescriptionItemStatus::Active->value)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['prescription_id', 'status'], 'prescription_items_prescription_status_idx');
        });

        Schema::create('prescription_allergy_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->cascadeOnDelete();
            $table->foreignId('patient_allergy_id')->nullable()->constrained('patient_allergies')->nullOnDelete();
            $table->foreignId('medication_id')->nullable()->constrained('medications')->nullOnDelete();
            $table->string('warning_type', 40);
            $table->string('severity', 40)->nullable();
            $table->text('message');
            $table->boolean('requires_acknowledgment')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('override_reason')->nullable();
            $table->timestamps();
            $table->index(['prescription_id', 'requires_acknowledgment'], 'presc_allergy_presc_ack_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_allergy_warnings');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
