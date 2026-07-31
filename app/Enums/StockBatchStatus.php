<?php

namespace App\Enums;

enum StockBatchStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case LowStock = 'low_stock';
    case Quarantined = 'quarantined';
    case Expired = 'expired';
    case Depleted = 'depleted';
    case Recalled = 'recalled';
    case Damaged = 'damaged';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
