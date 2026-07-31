<?php

namespace App\Services\Consultation;

use App\Enums\MedicalCertificateStatus;
use App\Models\Consultation;
use App\Models\MedicalCertificate;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Validation\ValidationException;

class MedicalCertificateService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly AuditLogService $audit,
    ) {}

    public function create(Consultation $consultation, array $data, User $actor): MedicalCertificate
    {
        $certificate = MedicalCertificate::create($data + [
            'certificate_number' => $this->references->medicalCertificateNumber(),
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'doctor_employee_id' => $consultation->doctor_employee_id,
            'status' => MedicalCertificateStatus::Draft,
        ]);

        $this->audit->record($actor, 'medical_certificate.created', 'medical-certificates', $certificate, 'Medical certificate draft created.');

        return $certificate;
    }

    public function update(MedicalCertificate $certificate, array $data, User $actor): MedicalCertificate
    {
        if ($certificate->status !== MedicalCertificateStatus::Draft) {
            throw ValidationException::withMessages(['certificate' => 'Only draft certificates can be updated.']);
        }

        $certificate->update($data);
        $this->audit->record($actor, 'medical_certificate.updated', 'medical-certificates', $certificate, 'Medical certificate draft updated.');

        return $certificate->refresh();
    }

    public function issue(MedicalCertificate $certificate, User $actor): MedicalCertificate
    {
        if ($certificate->status !== MedicalCertificateStatus::Draft) {
            throw ValidationException::withMessages(['certificate' => 'Only draft certificates can be issued.']);
        }

        $certificate->update([
            'status' => MedicalCertificateStatus::Issued,
            'issued_at' => now(),
            'issued_by' => $actor->id,
        ]);

        $this->audit->record($actor, 'medical_certificate.issued', 'medical-certificates', $certificate, 'Medical certificate issued.');

        return $certificate->refresh();
    }

    public function voidCertificate(MedicalCertificate $certificate, string $reason, User $actor): MedicalCertificate
    {
        if ($certificate->status === MedicalCertificateStatus::Void) {
            throw ValidationException::withMessages(['certificate' => 'This certificate is already void.']);
        }

        $certificate->update([
            'status' => MedicalCertificateStatus::Void,
            'voided_at' => now(),
            'voided_by' => $actor->id,
            'void_reason' => $reason,
        ]);

        $this->audit->record($actor, 'medical_certificate.voided', 'medical-certificates', $certificate, 'Medical certificate voided.');

        return $certificate->refresh();
    }
}
