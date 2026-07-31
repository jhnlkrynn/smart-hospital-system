<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_test_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_test_id')->constrained('laboratory_tests')->cascadeOnDelete();
            $table->foreignId('component_test_id')->constrained('laboratory_tests')->restrictOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['parent_test_id', 'component_test_id'], 'lab_test_components_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_test_components');
    }
};
