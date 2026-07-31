<?php

use App\Enums\AllergySeverity;
use App\Enums\AllergyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_allergies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('allergen');
            $table->string('allergy_type')->default(AllergyType::Other->value)->index();
            $table->string('reaction')->nullable();
            $table->string('severity')->default(AllergySeverity::Unknown->value)->index();
            $table->date('diagnosed_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
    }
};
