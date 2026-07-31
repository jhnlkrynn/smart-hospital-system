<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case OpeningBalance = 'opening_balance';
    case PurchaseReceipt = 'purchase_receipt';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Reservation = 'reservation';
    case ReservationRelease = 'reservation_release';
    case Quarantine = 'quarantine';
    case Unquarantine = 'unquarantine';
    case ExpiredWriteOff = 'expired_write_off';
    case DamagedWriteOff = 'damaged_write_off';
    case StockCountGain = 'stock_count_gain';
    case StockCountLoss = 'stock_count_loss';
    case ReturnToSupplier = 'return_to_supplier';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
