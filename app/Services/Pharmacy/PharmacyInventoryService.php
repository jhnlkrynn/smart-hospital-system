<?php

namespace App\Services\Pharmacy;

use App\Enums\InventoryTransactionType;
use App\Enums\PharmacyPurchaseStatus;
use App\Enums\PrescriptionItemStatus;
use App\Enums\PrescriptionStatus;
use App\Enums\StockBatchStatus;
use App\Models\MedicationStockBatch;
use App\Models\PharmacyInventoryTransaction;
use App\Models\PharmacyPurchase;
use App\Models\PharmacyStockAdjustment;
use App\Models\PharmacyStockReservation;
use App\Models\Prescription;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyInventoryService
{
    public function __construct(
        private readonly ReferenceNumberService $numbers,
        private readonly AuditLogService $audit,
    ) {}

    public function receivePurchase(PharmacyPurchase $purchase, array $receivedItems, int $locationId, User $actor): PharmacyPurchase
    {
        return DB::transaction(function () use ($purchase, $receivedItems, $locationId, $actor): PharmacyPurchase {
            foreach ($receivedItems as $received) {
                $item = $purchase->items()->whereKey($received['pharmacy_purchase_item_id'])->firstOrFail();
                $quantity = (float) $received['quantity'];
                if ($quantity <= 0) {
                    throw ValidationException::withMessages(['quantity' => 'Received quantity must be greater than zero.']);
                }

                $batch = MedicationStockBatch::create([
                    'medication_id' => $item->medication_id,
                    'pharmacy_location_id' => $locationId,
                    'pharmacy_supplier_id' => $purchase->pharmacy_supplier_id,
                    'pharmacy_purchase_item_id' => $item->id,
                    'lot_number' => $received['lot_number'],
                    'expiration_date' => $received['expiration_date'] ?? null,
                    'quantity_on_hand' => $quantity,
                    'quantity_reserved' => 0,
                    'unit_cost' => $received['unit_cost'] ?? $item->unit_cost,
                    'status' => StockBatchStatus::Available,
                ]);

                $item->increment('received_quantity', $quantity);
                $this->ledger($batch, InventoryTransactionType::PurchaseReceipt, $quantity, 0, $quantity, $actor, $purchase);
            }

            $purchase->refresh();
            $allReceived = $purchase->items->every(fn ($item): bool => (float) $item->received_quantity >= (float) $item->ordered_quantity);
            $purchase->forceFill([
                'status' => $allReceived ? PharmacyPurchaseStatus::Received : PharmacyPurchaseStatus::PartiallyReceived,
                'received_date' => now()->toDateString(),
                'updated_by' => $actor->id,
            ])->save();

            $this->audit->record($actor, 'received', 'pharmacy-purchases', $purchase, 'Received pharmacy purchase '.$purchase->purchase_number);

            return $purchase->refresh();
        });
    }

    public function adjust(MedicationStockBatch $batch, string $type, float $quantity, string $reason, User $actor): MedicationStockBatch
    {
        return DB::transaction(function () use ($batch, $type, $quantity, $reason, $actor): MedicationStockBatch {
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => 'Adjustment quantity must be greater than zero.']);
            }

            $before = (float) $batch->quantity_on_hand;
            $after = $type === 'increase' ? $before + $quantity : $before - $quantity;
            if ($after < (float) $batch->quantity_reserved) {
                throw ValidationException::withMessages(['quantity' => 'Adjustment would reduce stock below reserved quantity.']);
            }

            $batch->forceFill(['quantity_on_hand' => $after, 'status' => $after <= 0 ? StockBatchStatus::Depleted : StockBatchStatus::Available])->save();
            $adjustment = PharmacyStockAdjustment::create([
                'medication_stock_batch_id' => $batch->id,
                'medication_id' => $batch->medication_id,
                'adjustment_type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
                'adjusted_by' => $actor->id,
                'adjusted_at' => now(),
            ]);

            $this->ledger($batch, $type === 'increase' ? InventoryTransactionType::AdjustmentIncrease : InventoryTransactionType::AdjustmentDecrease, $quantity, $before, $after, $actor, $adjustment, $reason);
            $this->audit->record($actor, 'adjusted', 'pharmacy-inventory', $batch, 'Adjusted stock batch '.$batch->lot_number);

            return $batch->refresh();
        });
    }

    public function reserveForPrescription(Prescription $prescription, User $actor): Prescription
    {
        return DB::transaction(function () use ($prescription, $actor): Prescription {
            if (! $prescription->status->isFinalized()) {
                throw ValidationException::withMessages(['prescription' => 'Only finalized or reviewed prescriptions can be reserved.']);
            }

            foreach ($prescription->items()->where('status', PrescriptionItemStatus::Active->value)->get() as $item) {
                $needed = (float) $item->quantity - (float) $item->reservationQuantity();
                if ($needed <= 0) {
                    continue;
                }

                $batches = MedicationStockBatch::query()
                    ->where('medication_id', $item->medication_id)
                    ->reservable()
                    ->orderByRaw('expiration_date is null')
                    ->orderBy('expiration_date')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($needed <= 0) {
                        break;
                    }

                    $available = (float) $batch->quantity_on_hand - (float) $batch->quantity_reserved;
                    $reserved = min($available, $needed);
                    if ($reserved <= 0) {
                        continue;
                    }

                    $before = (float) $batch->quantity_reserved;
                    $batch->increment('quantity_reserved', $reserved);
                    PharmacyStockReservation::create([
                        'prescription_id' => $prescription->id,
                        'prescription_item_id' => $item->id,
                        'medication_id' => $item->medication_id,
                        'medication_stock_batch_id' => $batch->id,
                        'quantity_reserved' => $reserved,
                        'reserved_at' => now(),
                        'reserved_by' => $actor->id,
                    ]);
                    $this->ledger($batch->refresh(), InventoryTransactionType::Reservation, $reserved, $before, (float) $batch->quantity_reserved, $actor, $prescription);
                    $needed -= $reserved;
                }

                $item->forceFill(['status' => $needed <= 0 ? PrescriptionItemStatus::Reserved : PrescriptionItemStatus::PartiallyReserved])->save();
            }

            $total = $prescription->items()->count();
            $reserved = $prescription->items()->where('status', PrescriptionItemStatus::Reserved->value)->count();
            $prescription->forceFill(['status' => $reserved === $total ? PrescriptionStatus::Reserved : PrescriptionStatus::PartiallyReserved])->save();
            $this->audit->record($actor, 'reserved', 'pharmacy-inventory', $prescription, 'Reserved stock for prescription '.$prescription->prescription_number);

            return $prescription->refresh();
        });
    }

    public function releaseReservation(PharmacyStockReservation $reservation, User $actor, string $reason): PharmacyStockReservation
    {
        return DB::transaction(function () use ($reservation, $actor, $reason): PharmacyStockReservation {
            if ($reservation->released_at) {
                return $reservation;
            }

            $batch = $reservation->stockBatch()->lockForUpdate()->firstOrFail();
            $before = (float) $batch->quantity_reserved;
            $batch->decrement('quantity_reserved', (float) $reservation->quantity_reserved);
            $reservation->forceFill(['released_at' => now(), 'released_by' => $actor->id, 'release_reason' => $reason])->save();
            $this->ledger($batch->refresh(), InventoryTransactionType::ReservationRelease, (float) $reservation->quantity_reserved, $before, (float) $batch->quantity_reserved, $actor, $reservation, $reason);

            return $reservation->refresh();
        });
    }

    public function quarantine(MedicationStockBatch $batch, User $actor, string $reason): MedicationStockBatch
    {
        $batch->forceFill(['status' => StockBatchStatus::Quarantined, 'quarantined_at' => now(), 'quarantined_by' => $actor->id, 'quarantine_reason' => $reason])->save();
        $this->ledger($batch, InventoryTransactionType::Quarantine, 0, (float) $batch->quantity_on_hand, (float) $batch->quantity_on_hand, $actor, $batch, $reason);

        return $batch->refresh();
    }

    public function unquarantine(MedicationStockBatch $batch, User $actor): MedicationStockBatch
    {
        $batch->forceFill(['status' => StockBatchStatus::Available, 'quarantined_at' => null, 'quarantined_by' => null, 'quarantine_reason' => null])->save();
        $this->ledger($batch, InventoryTransactionType::Unquarantine, 0, (float) $batch->quantity_on_hand, (float) $batch->quantity_on_hand, $actor, $batch);

        return $batch->refresh();
    }

    private function ledger(MedicationStockBatch $batch, InventoryTransactionType $type, float $quantity, float $before, float $after, User $actor, mixed $reference = null, ?string $reason = null): PharmacyInventoryTransaction
    {
        return PharmacyInventoryTransaction::create([
            'transaction_number' => $this->numbers->inventoryTransactionNumber(),
            'medication_id' => $batch->medication_id,
            'medication_stock_batch_id' => $batch->id,
            'pharmacy_location_id' => $batch->pharmacy_location_id,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'unit_cost' => $batch->unit_cost,
            'reference_type' => is_object($reference) ? $reference::class : null,
            'reference_id' => is_object($reference) ? $reference->id : null,
            'reason' => $reason,
            'performed_by' => $actor->id,
            'occurred_at' => now(),
        ]);
    }
}
