<?php

namespace App\Enums;

enum AppointmentSource: string
{
    case PatientPortal = 'patient_portal';
    case Staff = 'staff';
    case Admin = 'admin';
    case Doctor = 'doctor';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::PatientPortal => 'Patient portal',
            self::Staff => 'Staff',
            self::Admin => 'Admin',
            self::Doctor => 'Doctor',
            self::WalkIn => 'Walk-in',
        };
    }
}
