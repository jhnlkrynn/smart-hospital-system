<?php

namespace Database\Factories;

use App\Enums\PharmacyPurchaseStatus;
use App\Models\PharmacyPurchase;
use App\Models\PharmacySupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyPurchaseFactory extends Factory
{
    protected $model = PharmacyPurchase::class;

    public function definition(): array
    {
        return ['purchase_number' => strtoupper(fake()->unique()->bothify('PO-20260731-#####')), 'pharmacy_supplier_id' => PharmacySupplier::factory(), 'status' => PharmacyPurchaseStatus::Ordered, 'order_date' => now()->toDateString()];
    }
}
