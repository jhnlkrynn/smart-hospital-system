<?php

use App\Enums\PatientStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table): void {
            $table->id();
            $table->string('patient_number')->unique();
            $table->string('qr_token', 128)->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('first_name')->index();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->index();
            $table->string('suffix')->nullable();
            $table->date('date_of_birth');
            $table->string('sex', 30)->index();
            $table->string('civil_status', 50)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('contact_number', 50)->nullable()->index();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_number')->nullable();
            $table->date('registration_date')->index();
            $table->string('status')->default(PatientStatus::Active->value)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
