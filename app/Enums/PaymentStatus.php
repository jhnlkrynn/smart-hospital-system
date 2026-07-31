<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}
