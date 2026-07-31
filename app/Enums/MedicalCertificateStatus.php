<?php

namespace App\Enums;

enum MedicalCertificateStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued',
            self::Void => 'Void',
        };
    }
}
