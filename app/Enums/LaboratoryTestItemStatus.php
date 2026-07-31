<?php

namespace App\Enums;

enum LaboratoryTestItemStatus: string
{
    case Pending = 'pending';
    case SpecimenCollected = 'specimen_collected';
    case InProcess = 'in_process';
    case ResultEntered = 'result_entered';
    case Verified = 'verified';
    case Released = 'released';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::SpecimenCollected => 'Specimen collected',
            self::InProcess => 'In process',
            self::ResultEntered => 'Result entered',
            self::Verified => 'Verified',
            self::Released => 'Released',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
        };
    }
}
