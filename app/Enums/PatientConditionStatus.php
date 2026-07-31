<?php

namespace App\Enums;

enum PatientConditionStatus: string
{
    case Active = 'active';
    case Managed = 'managed';
    case Resolved = 'resolved';
    case Unknown = 'unknown';
}
