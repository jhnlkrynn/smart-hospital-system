<?php

namespace App\Enums;

enum MedicationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Discontinued = 'discontinued';
    case Restricted = 'restricted';
    case OutOfFormulary = 'out_of_formulary';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Discontinued => 'Discontinued',
            self::Restricted => 'Restricted',
            self::OutOfFormulary => 'Out of formulary',
        };
    }

    public function canBePrescribed(): bool
    {
        return in_array($this, [self::Active, self::Restricted], true);
    }

    public function requiresSpecialPermission(): bool
    {
        return $this === self::Restricted;
    }
}
