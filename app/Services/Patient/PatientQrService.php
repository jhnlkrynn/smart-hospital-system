<?php

namespace App\Services\Patient;

use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PatientQrService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Patient::where('qr_token', $token)->exists());

        return $token;
    }

    public function regenerateToken(Patient $patient, User $actor): Patient
    {
        return DB::transaction(function () use ($patient, $actor): Patient {
            $patient->forceFill(['qr_token' => $this->generateToken(), 'updated_by' => $actor->id])->save();

            $this->auditLog->record($actor, 'qr_regenerated', 'patients', $patient, 'Patient QR token regenerated.', null, ['qr_token' => '[masked]'], request());

            return $patient;
        });
    }

    public function generateSignedLookupUrl(Patient $patient): string
    {
        return URL::temporarySignedRoute('patient-lookup.show', now()->addYears(2), ['token' => $patient->qr_token]);
    }

    public function generateQrImage(Patient $patient): string
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: $this->generateSignedLookupUrl($patient),
            size: 260,
            margin: 10,
        ))->build();

        return $result->getDataUri();
    }
}
