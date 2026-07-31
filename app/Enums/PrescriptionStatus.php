<?php

namespace App\Enums;

enum PrescriptionStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';
    case Reviewed = 'reviewed';
    case PartiallyReserved = 'partially_reserved';
    case Reserved = 'reserved';
    case Cancelled = 'cancelled';
    case Replaced = 'replaced';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Finalized => 'Finalized',
            self::Reviewed => 'Reviewed',
            self::PartiallyReserved => 'Partially reserved',
            self::Reserved => 'Reserved',
            self::Cancelled => 'Cancelled',
            self::Replaced => 'Replaced',
            self::Expired => 'Expired',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::Finalized, self::Reviewed, self::PartiallyReserved, self::Reserved], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Replaced, self::Expired], true);
    }
}
