<?php

namespace App\Enums;

enum PharmacyPurchaseStatus: string
{
    case Draft = 'draft';
    case Ordered = 'ordered';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
