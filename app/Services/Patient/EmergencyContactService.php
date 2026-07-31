<?php

namespace App\Services\Patient;

use App\Models\Patient;
use App\Models\PatientEmergencyContact;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class EmergencyContactService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function create(Patient $patient, array $data, User $actor): PatientEmergencyContact
    {
        return DB::transaction(function () use ($patient, $data, $actor): PatientEmergencyContact {
            if ($data['is_primary'] ?? false) {
                $patient->emergencyContacts()->update(['is_primary' => false]);
            }
            $contact = $patient->emergencyContacts()->create($data);
            $this->auditLog->record($actor, 'contact_created', 'patients', $patient, 'Emergency contact created.', null, $contact->toArray(), request());

            return $contact;
        });
    }

    public function update(PatientEmergencyContact $contact, array $data, User $actor): PatientEmergencyContact
    {
        return DB::transaction(function () use ($contact, $data, $actor): PatientEmergencyContact {
            if ($data['is_primary'] ?? false) {
                $contact->patient->emergencyContacts()->whereKeyNot($contact->id)->update(['is_primary' => false]);
            }
            $old = $contact->toArray();
            $contact->update($data);
            $this->auditLog->record($actor, 'contact_updated', 'patients', $contact->patient, 'Emergency contact updated.', $old, $contact->fresh()->toArray(), request());

            return $contact;
        });
    }
}
