<?php

namespace Tests\Feature\Pharmacy;

use App\Enums\PharmacyPurchaseStatus;
use App\Models\Medication;
use App\Models\PharmacyLocation;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySupplier;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacist_receives_purchase_into_batch_and_ledger(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('pharmacist');
        $purchase = PharmacyPurchase::factory()->create(['pharmacy_supplier_id' => PharmacySupplier::factory()]);
        $item = $purchase->items()->create(['medication_id' => Medication::factory()->create()->id, 'ordered_quantity' => 20, 'unit_cost' => 3]);
        $location = PharmacyLocation::factory()->create();

        $this->actingAs($user)->post(route('pharmacist.purchases.receive', $purchase), [
            'pharmacy_location_id' => $location->id,
            'received_items' => [[
                'pharmacy_purchase_item_id' => $item->id,
                'quantity' => 20,
                'lot_number' => 'LOT-RX-1',
                'expiration_date' => now()->addYear()->toDateString(),
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('pharmacy_purchases', ['id' => $purchase->id, 'status' => PharmacyPurchaseStatus::Received->value]);
        $this->assertDatabaseHas('medication_stock_batches', ['lot_number' => 'LOT-RX-1', 'quantity_on_hand' => 20]);
        $this->assertDatabaseHas('pharmacy_inventory_transactions', ['quantity' => 20]);
    }
}
