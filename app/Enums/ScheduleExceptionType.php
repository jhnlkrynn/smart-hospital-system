<?php

namespace App\Enums;

enum ScheduleExceptionType: string
{
    case Leave = 'leave';
    case Unavailable = 'unavailable';
    case CustomHours = 'custom_hours';
    case Holiday = 'holiday';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Leave => 'Leave',
            self::Unavailable => 'Unavailable',
            self::CustomHours => 'Custom hours',
            self::Holiday => 'Holiday',
            self::Emergency => 'Emergency',
        };
    }
}
