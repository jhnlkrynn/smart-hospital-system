<?php

namespace App\Enums;

enum TriageAcuity: string
{
    case Emergency = 'emergency';
    case Urgent = 'urgent';
    case Priority = 'priority';
    case Routine = 'routine';

    public function label(): string
    {
        return match ($this) {
            self::Emergency => 'Emergency',
            self::Urgent => 'Urgent',
            self::Priority => 'Priority',
            self::Routine => 'Routine',
        };
    }
}
