<?php

namespace App\Enums;

enum PrescriptionItemStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Replaced = 'replaced';
    case PartiallyReserved = 'partially_reserved';
    case Reserved = 'reserved';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
