<?php

namespace App\Enums;

enum DiagnosisType: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Provisional = 'provisional';
    case Differential = 'differential';
    case Historical = 'historical';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primary',
            self::Secondary => 'Secondary',
            self::Provisional => 'Provisional',
            self::Differential => 'Differential',
            self::Historical => 'Historical',
        };
    }

    public function syncsToProblemList(): bool
    {
        return in_array($this, [self::Primary, self::Secondary], true);
    }
}
