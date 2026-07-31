<?php

namespace App\Enums;

enum LaboratoryAbnormalFlag: string
{
    case Normal = 'normal';
    case Low = 'low';
    case High = 'high';
    case CriticalLow = 'critical_low';
    case CriticalHigh = 'critical_high';
    case Abnormal = 'abnormal';
    case Indeterminate = 'indeterminate';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Low => 'Low',
            self::High => 'High',
            self::CriticalLow => 'Critical low',
            self::CriticalHigh => 'Critical high',
            self::Abnormal => 'Abnormal',
            self::Indeterminate => 'Indeterminate',
            self::NotApplicable => 'Not applicable',
        };
    }

    public function isCritical(): bool
    {
        return in_array($this, [self::CriticalLow, self::CriticalHigh], true);
    }
}
