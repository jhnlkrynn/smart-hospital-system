<?php

namespace Tests\Feature\Pharmacy;

use App\Enums\PrescriptionStatus;
use App\Models\MedicationStockBatch;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacist_reserves_stock_without_reducing_on_hand_quantity(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('pharmacist');
        $prescription = Prescription::factory()->create(['status' => PrescriptionStatus::Finalized]);
        $item = PrescriptionItem::factory()->create(['prescription_id' => $prescription->id, 'quantity' => 8]);
        $batch = MedicationStockBatch::factory()->create(['medication_id' => $item->medication_id, 'quantity_on_hand' => 20, 'quantity_reserved' => 0]);

        $this->actingAs($user)->post(route('pharmacist.prescriptions.reserve', $prescription))->assertRedirect();

        $this->assertDatabaseHas('medication_stock_batches', ['id' => $batch->id, 'quantity_on_hand' => 20, 'quantity_reserved' => 8]);
        $this->assertDatabaseHas('pharmacy_stock_reservations', ['prescription_item_id' => $item->id, 'quantity_reserved' => 8]);
    }
}
