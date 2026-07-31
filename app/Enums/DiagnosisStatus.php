<?php

namespace App\Enums;

enum DiagnosisStatus: string
{
    case Active = 'active';
    case Resolved = 'resolved';
    case RuledOut = 'ruled_out';
    case Chronic = 'chronic';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Resolved => 'Resolved',
            self::RuledOut => 'Ruled out',
            self::Chronic => 'Chronic',
            self::Inactive => 'Inactive',
        };
    }
}
