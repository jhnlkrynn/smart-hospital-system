<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_result_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_result_id')->constrained('laboratory_results')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->text('amendment_reason')->nullable();
            $table->foreignId('amended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('amended_at')->nullable();
            $table->timestamps();
            $table->unique(['laboratory_result_id', 'version'], 'lab_result_versions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_result_versions');
    }
};
