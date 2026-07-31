<?php

use App\Enums\LaboratoryResultType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_test_category_id')->constrained()->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->string('result_type', 30)->default(LaboratoryResultType::Numeric->value)->index();
            $table->string('default_unit', 50)->nullable();
            $table->foreignId('specimen_type_id')->nullable()->constrained('specimen_types')->nullOnDelete();
            $table->string('minimum_specimen_volume', 50)->nullable();
            $table->text('collection_instructions')->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->unsignedInteger('estimated_turnaround_minutes')->nullable();
            $table->boolean('requires_fasting')->default(false);
            $table->boolean('requires_verification')->default(true);
            $table->boolean('is_panel')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['laboratory_test_category_id', 'is_active'], 'lab_tests_category_active_idx');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_tests');
    }
};
