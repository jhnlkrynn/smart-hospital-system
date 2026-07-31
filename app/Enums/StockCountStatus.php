<?php

namespace App\Enums;

enum StockCountStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Reconciled = 'reconciled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
