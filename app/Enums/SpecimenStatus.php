<?php

namespace App\Enums;

enum SpecimenStatus: string
{
    case Pending = 'pending';
    case Collected = 'collected';
    case Received = 'received';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case RecollectionRequired = 'recollection_required';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Collected => 'Collected',
            self::Received => 'Received',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::RecollectionRequired => 'Recollection required',
            self::Disposed => 'Disposed',
        };
    }
}
