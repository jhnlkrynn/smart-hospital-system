<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_specimen_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laboratory_specimen_id')->constrained('laboratory_specimens')->cascadeOnDelete();
            $table->foreignId('laboratory_request_item_id')->constrained('laboratory_request_items')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['laboratory_specimen_id', 'laboratory_request_item_id'], 'lab_specimen_items_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_specimen_items');
    }
};
