<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Rescheduled = 'rescheduled';
    case CheckedIn = 'checked_in';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Rescheduled => 'Rescheduled',
            self::CheckedIn => 'Checked in',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::NoShow => 'No-show',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Rejected, self::Cancelled, self::Rescheduled, self::Completed, self::NoShow], true);
    }

    public function blocksSlot(): bool
    {
        return ! in_array($this, [self::Rejected, self::Cancelled, self::Rescheduled, self::NoShow], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Approved], true);
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Approved], true);
    }
}
