<?php

namespace App\Enums;

enum BillStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}
