<?php

namespace App\Enums;

enum PatientDocumentType: string
{
    case Identification = 'identification';
    case Insurance = 'insurance';
    case Referral = 'referral';
    case Consent = 'consent';
    case MedicalAttachment = 'medical_attachment';
    case Other = 'other';
}
