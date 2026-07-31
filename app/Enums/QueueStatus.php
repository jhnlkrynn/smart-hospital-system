<?php

namespace App\Enums;

enum QueueStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case InTriage = 'in_triage';
    case Triaged = 'triaged';
    case WithDoctor = 'with_doctor';
    case Completed = 'completed';
    case Skipped = 'skipped';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::Called => 'Called',
            self::InTriage => 'In triage',
            self::Triaged => 'Triaged',
            self::WithDoctor => 'With doctor',
            self::Completed => 'Completed',
            self::Skipped => 'Skipped',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No-show',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::NoShow], true);
    }
}
