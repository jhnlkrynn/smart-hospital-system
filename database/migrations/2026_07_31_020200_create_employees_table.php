<?php

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_number')->unique();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->index();
            $table->string('suffix')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('sex', 30)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->string('email')->unique();
            $table->string('contact_number', 50)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('position')->index();
            $table->string('employment_type')->default(EmploymentType::Regular->value)->index();
            $table->string('employment_status')->default(EmploymentStatus::Active->value)->index();
            $table->date('hire_date')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiration_date')->nullable();
            $table->string('specialization')->nullable();
            $table->decimal('consultation_fee', 12, 2)->nullable();
            $table->unsignedSmallInteger('maximum_appointments_per_day')->nullable();
            $table->string('clinic_room')->nullable();
            $table->text('work_schedule_notes')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_number', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['department_id', 'employment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
