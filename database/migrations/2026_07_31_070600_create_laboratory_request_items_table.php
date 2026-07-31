<?php

use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryTestItemStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('laboratory_test_id')->constrained('laboratory_tests')->restrictOnDelete();
            $table->string('test_code_snapshot', 50);
            $table->string('test_name_snapshot');
            $table->string('result_type_snapshot', 30);
            $table->string('unit_snapshot', 50)->nullable();
            $table->foreignId('specimen_type_id')->nullable()->constrained('specimen_types')->nullOnDelete();
            $table->string('priority', 30)->default(LaboratoryPriority::Routine->value)->index();
            $table->string('status', 40)->default(LaboratoryTestItemStatus::Pending->value)->index();
            $table->text('requested_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['laboratory_request_id', 'laboratory_test_id', 'deleted_at'], 'lab_request_items_unique_active');
            $table->index(['laboratory_request_id', 'status'], 'lab_request_items_request_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_request_items');
    }
};
