<?php

use App\Enums\SpecimenStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_specimens', function (Blueprint $table): void {
            $table->id();
            $table->string('accession_number')->unique();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('specimen_type_id')->constrained('specimen_types')->restrictOnDelete();
            $table->string('status', 40)->default(SpecimenStatus::Pending->value)->index();
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('recollection_due_at')->nullable();
            $table->text('collection_notes')->nullable();
            $table->string('container_type')->nullable();
            $table->string('specimen_volume', 50)->nullable();
            $table->string('specimen_unit', 30)->nullable();
            $table->string('barcode_value')->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['laboratory_request_id', 'status'], 'lab_specimens_request_status_idx');
            $table->index(['patient_id', 'status'], 'lab_specimens_patient_status_idx');
            $table->index('collected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_specimens');
    }
};
