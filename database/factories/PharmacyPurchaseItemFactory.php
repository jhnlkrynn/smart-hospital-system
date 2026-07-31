<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\PharmacyPurchase;
use App\Models\PharmacyPurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyPurchaseItemFactory extends Factory
{
    protected $model = PharmacyPurchaseItem::class;

    public function definition(): array
    {
        return ['pharmacy_purchase_id' => PharmacyPurchase::factory(), 'medication_id' => Medication::factory(), 'ordered_quantity' => 50, 'received_quantity' => 0, 'unit_cost' => 2.5];
    }
}
