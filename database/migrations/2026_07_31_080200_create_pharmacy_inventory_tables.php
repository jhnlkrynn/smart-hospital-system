<?php

use App\Enums\InventoryTransactionType;
use App\Enums\PharmacyPurchaseStatus;
use App\Enums\StockBatchStatus;
use App\Enums\StockCountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_quarantine')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pharmacy_purchases', function (Blueprint $table): void {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('pharmacy_supplier_id')->constrained()->restrictOnDelete();
            $table->string('status', 40)->default(PharmacyPurchaseStatus::Draft->value)->index();
            $table->date('order_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pharmacy_purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pharmacy_purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->decimal('ordered_quantity', 12, 3);
            $table->decimal('received_quantity', 12, 3)->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('medication_units')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('medication_stock_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->foreignId('pharmacy_location_id')->constrained()->restrictOnDelete();
            $table->foreignId('pharmacy_supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pharmacy_purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number');
            $table->date('expiration_date')->nullable()->index();
            $table->decimal('quantity_on_hand', 12, 3)->default(0);
            $table->decimal('quantity_reserved', 12, 3)->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('status', 40)->default(StockBatchStatus::Available->value)->index();
            $table->timestamp('quarantined_at')->nullable();
            $table->foreignId('quarantined_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('quarantine_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['medication_id', 'pharmacy_location_id', 'lot_number'], 'stock_batch_med_loc_lot_unique');
            $table->index(['medication_id', 'status'], 'stock_batches_med_status_idx');
        });

        Schema::create('pharmacy_inventory_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->foreignId('medication_stock_batch_id')->nullable();
            $table->foreignId('pharmacy_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type', 50)->default(InventoryTransactionType::OpeningBalance->value)->index();
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_before', 12, 3)->nullable();
            $table->decimal('quantity_after', 12, 3)->nullable();
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->foreign('medication_stock_batch_id', 'pharm_txn_stock_batch_fk')->references('id')->on('medication_stock_batches')->nullOnDelete();
            $table->index(['reference_type', 'reference_id'], 'pharmacy_txn_reference_idx');
        });

        Schema::create('pharmacy_stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->constrained('prescription_items')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->foreignId('medication_stock_batch_id');
            $table->decimal('quantity_reserved', 12, 3);
            $table->timestamp('reserved_at');
            $table->foreignId('reserved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_reason')->nullable();
            $table->timestamps();
            $table->foreign('medication_stock_batch_id', 'stock_res_stock_batch_fk')->references('id')->on('medication_stock_batches')->restrictOnDelete();
            $table->index(['prescription_id', 'released_at'], 'stock_reservations_prescription_released_idx');
        });

        Schema::create('pharmacy_stock_transfers', function (Blueprint $table): void {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_location_id')->constrained('pharmacy_locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('pharmacy_locations')->restrictOnDelete();
            $table->string('status', 40)->default('draft')->index();
            $table->text('reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pharmacy_stock_transfer_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pharmacy_stock_transfer_id');
            $table->foreignId('medication_stock_batch_id');
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->timestamps();
            $table->foreign('pharmacy_stock_transfer_id', 'stock_transfer_items_transfer_fk')->references('id')->on('pharmacy_stock_transfers')->cascadeOnDelete();
            $table->foreign('medication_stock_batch_id', 'stock_transfer_items_batch_fk')->references('id')->on('medication_stock_batches')->restrictOnDelete();
        });

        Schema::create('pharmacy_stock_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medication_stock_batch_id');
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->string('adjustment_type', 30);
            $table->decimal('quantity', 12, 3);
            $table->text('reason');
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at');
            $table->timestamps();
            $table->foreign('medication_stock_batch_id', 'stock_adjustments_batch_fk')->references('id')->on('medication_stock_batches')->restrictOnDelete();
        });

        Schema::create('pharmacy_stock_counts', function (Blueprint $table): void {
            $table->id();
            $table->string('stock_count_number')->unique();
            $table->foreignId('pharmacy_location_id')->constrained()->restrictOnDelete();
            $table->string('status', 40)->default(StockCountStatus::Draft->value)->index();
            $table->date('count_date');
            $table->text('notes')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pharmacy_stock_count_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pharmacy_stock_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_stock_batch_id');
            $table->foreignId('medication_id')->constrained('medications')->restrictOnDelete();
            $table->decimal('system_quantity', 12, 3);
            $table->decimal('physical_quantity', 12, 3)->nullable();
            $table->decimal('variance_quantity', 12, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('medication_stock_batch_id', 'stock_count_items_batch_fk')->references('id')->on('medication_stock_batches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stock_count_items');
        Schema::dropIfExists('pharmacy_stock_counts');
        Schema::dropIfExists('pharmacy_stock_adjustments');
        Schema::dropIfExists('pharmacy_stock_transfer_items');
        Schema::dropIfExists('pharmacy_stock_transfers');
        Schema::dropIfExists('pharmacy_stock_reservations');
        Schema::dropIfExists('pharmacy_inventory_transactions');
        Schema::dropIfExists('medication_stock_batches');
        Schema::dropIfExists('pharmacy_purchase_items');
        Schema::dropIfExists('pharmacy_purchases');
        Schema::dropIfExists('pharmacy_locations');
    }
};
