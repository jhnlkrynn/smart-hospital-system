<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_reference_ranges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_test_id')->constrained('laboratory_tests')->cascadeOnDelete();
            $table->string('sex', 30)->nullable()->index();
            $table->unsignedInteger('minimum_age_days')->nullable();
            $table->unsignedInteger('maximum_age_days')->nullable();
            $table->decimal('lower_bound', 12, 4)->nullable();
            $table->decimal('upper_bound', 12, 4)->nullable();
            $table->decimal('critical_lower_bound', 12, 4)->nullable();
            $table->decimal('critical_upper_bound', 12, 4)->nullable();
            $table->string('text_reference')->nullable();
            $table->string('unit', 50)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['laboratory_test_id', 'is_active'], 'lab_ranges_test_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_reference_ranges');
    }
};
