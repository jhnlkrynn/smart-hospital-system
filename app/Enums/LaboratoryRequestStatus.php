<?php

namespace App\Enums;

enum LaboratoryRequestStatus: string
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case SpecimenCollected = 'specimen_collected';
    case Processing = 'processing';
    case Completed = 'completed';
    case Released = 'released';
    case Cancelled = 'cancelled';
}
