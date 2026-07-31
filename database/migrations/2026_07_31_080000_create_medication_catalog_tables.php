<?php

use App\Enums\MedicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'medication_categories' => ['display_order' => true],
            'dosage_forms' => [],
            'medication_routes' => [],
        ] as $tableName => $options) {
            Schema::create($tableName, function (Blueprint $table) use ($options): void {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                if ($options['display_order'] ?? false) {
                    $table->unsignedInteger('display_order')->default(0)->index();
                }
                $table->boolean('is_active')->default(true)->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('medication_units', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name')->unique();
            $table->string('symbol', 30)->nullable();
            $table->string('unit_type', 50)->default('quantity')->index();
            $table->unsignedTinyInteger('decimal_places')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medication_frequencies', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('abbreviation', 30)->nullable();
            $table->decimal('times_per_day', 8, 2)->nullable();
            $table->unsignedInteger('interval_hours')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medication_manufacturers', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pharmacy_suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('supplier_number')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('license_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
        });

        Schema::create('medications', function (Blueprint $table): void {
            $table->id();
            $table->string('medication_number')->unique();
            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->foreignId('medication_category_id')->constrained('medication_categories')->restrictOnDelete();
            $table->foreignId('dosage_form_id')->nullable()->constrained('dosage_forms')->nullOnDelete();
            $table->decimal('strength_value', 12, 4)->nullable();
            $table->foreignId('strength_unit_id')->nullable()->constrained('medication_units')->nullOnDelete();
            $table->string('concentration_text')->nullable();
            $table->foreignId('manufacturer_id')->nullable()->constrained('medication_manufacturers')->nullOnDelete();
            $table->string('status', 40)->default(MedicationStatus::Active->value)->index();
            $table->string('formulary_status', 40)->default('formulary')->index();
            $table->text('description')->nullable();
            $table->text('indications_summary')->nullable();
            $table->text('storage_requirements')->nullable();
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_controlled')->default(false)->index();
            $table->boolean('is_high_alert')->default(false)->index();
            $table->boolean('requires_cold_storage')->default(false);
            $table->foreignId('default_route_id')->nullable()->constrained('medication_routes')->nullOnDelete();
            $table->foreignId('default_frequency_id')->nullable()->constrained('medication_frequencies')->nullOnDelete();
            $table->decimal('default_reorder_level', 12, 3)->default(0);
            $table->decimal('default_minimum_stock', 12, 3)->default(0);
            $table->decimal('default_maximum_stock', 12, 3)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('generic_name');
            $table->index('brand_name');
            $table->index(['medication_category_id', 'is_active'], 'medications_category_active_idx');
        });

        Schema::create('medication_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('alias_name');
            $table->string('alias_type', 40)->default('common');
            $table->timestamps();
            $table->unique(['medication_id', 'alias_name'], 'medication_alias_unique');
        });

        Schema::create('medication_allergy_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medication_allergy_group_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('medication_allergy_group_id');
            $table->timestamps();
            $table->foreign('medication_allergy_group_id', 'med_allergy_group_items_group_fk')->references('id')->on('medication_allergy_groups')->cascadeOnDelete();
            $table->unique(['medication_id', 'medication_allergy_group_id'], 'med_allergy_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_allergy_group_items');
        Schema::dropIfExists('medication_allergy_groups');
        Schema::dropIfExists('medication_aliases');
        Schema::dropIfExists('medications');
        Schema::dropIfExists('pharmacy_suppliers');
        Schema::dropIfExists('medication_manufacturers');
        Schema::dropIfExists('medication_frequencies');
        Schema::dropIfExists('medication_units');
        Schema::dropIfExists('medication_routes');
        Schema::dropIfExists('dosage_forms');
        Schema::dropIfExists('medication_categories');
    }
};
