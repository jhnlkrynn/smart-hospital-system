<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::InProgress => 'In progress',
            self::Paused => 'Paused',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Reopened => 'Reopened',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Waiting, self::InProgress, self::Paused, self::Reopened], true);
    }

    public function isFinalized(): bool
    {
        return $this === self::Completed;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
