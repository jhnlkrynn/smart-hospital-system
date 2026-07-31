<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_schedule_exceptions')) {
            return;
        }

        Schema::create('doctor_schedule_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('exception_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('exception_type', 30)->index();
            $table->text('reason')->nullable();
            $table->boolean('is_available')->default(false);
            $table->unsignedSmallInteger('maximum_appointments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['doctor_employee_id', 'exception_date'], 'doc_sched_ex_doc_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_exceptions');
    }
};
