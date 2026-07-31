<?php

namespace App\Enums;

enum QueuePriority: string
{
    case Emergency = 'emergency';
    case Pregnant = 'pregnant';
    case Pwd = 'pwd';
    case SeniorCitizen = 'senior_citizen';
    case Routine = 'routine';

    public function label(): string
    {
        return match ($this) {
            self::Emergency => 'Emergency',
            self::Pregnant => 'Pregnant',
            self::Pwd => 'PWD',
            self::SeniorCitizen => 'Senior Citizen',
            self::Routine => 'Routine',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::Emergency => 1,
            self::Pregnant => 2,
            self::Pwd => 3,
            self::SeniorCitizen => 4,
            self::Routine => 5,
        };
    }
}
