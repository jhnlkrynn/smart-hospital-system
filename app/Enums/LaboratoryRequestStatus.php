<?php

namespace App\Enums;

enum LaboratoryRequestStatus: string
{
    case Draft = 'draft';
    case Requested = 'requested';
    case Received = 'received';
    case SpecimenPending = 'specimen_pending';
    case SpecimenCollected = 'specimen_collected';
    case InProcess = 'in_process';
    case PartiallyCompleted = 'partially_completed';
    case Completed = 'completed';
    case Verified = 'verified';
    case Released = 'released';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case RecollectionRequired = 'recollection_required';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Requested => 'Requested',
            self::Received => 'Received',
            self::SpecimenPending => 'Specimen pending',
            self::SpecimenCollected => 'Specimen collected',
            self::InProcess => 'In process',
            self::PartiallyCompleted => 'Partially completed',
            self::Completed => 'Completed',
            self::Verified => 'Verified',
            self::Released => 'Released',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
            self::RecollectionRequired => 'Recollection required',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Requested, self::SpecimenPending, self::RecollectionRequired], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Released, self::Cancelled, self::Rejected], true);
    }

    public function canCollectSpecimen(): bool
    {
        return in_array($this, [self::Requested, self::Received, self::SpecimenPending, self::RecollectionRequired], true);
    }

    public function canEnterResults(): bool
    {
        return in_array($this, [self::SpecimenCollected, self::InProcess, self::PartiallyCompleted], true);
    }
}
