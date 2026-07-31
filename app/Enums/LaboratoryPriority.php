<?php

namespace App\Enums;

enum LaboratoryPriority: string
{
    case Routine = 'routine';
    case Urgent = 'urgent';
    case Stat = 'stat';

    public function label(): string
    {
        return match ($this) {
            self::Routine => 'Routine',
            self::Urgent => 'Urgent',
            self::Stat => 'STAT',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Routine => 'Normal laboratory processing.',
            self::Urgent => 'Expedited processing according to hospital policy.',
            self::Stat => 'Immediate priority according to hospital policy.',
        };
    }
}
