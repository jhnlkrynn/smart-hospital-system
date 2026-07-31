<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';
    case Dispensed = 'dispensed';
    case Damaged = 'damaged';
    case Expired = 'expired';
    case Returned = 'returned';
}
