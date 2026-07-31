<?php

namespace App\Services\Patient;

use App\Enums\PatientStatus;
use App\Enums\UserStatus;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PatientService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly PatientQrService $qr,
        private readonly AuditLogService $auditLog,
    ) {
    }

    public function createWithAccount(array $data, User $actor): Patient
    {
        return $this->createPatient($data, $actor, true);
    }

    public function createWithoutAccount(array $data, User $actor): Patient
    {
        return $this->createPatient($data, $actor, false);
    }

    public function createFromRegistration(User $user, array $data): Patient
    {
        return DB::transaction(function () use ($user, $data): Patient {
            if ($user->patient()->exists()) {
                return $user->patient;
            }

            $patient = new Patient($this->patientData($data));
            $patient->patient_number = $this->references->patientNumber();
            $patient->qr_token = $this->qr->generateToken();
            $patient->user_id = $user->id;
            $patient->email = $user->email;
            $patient->registration_date = now('Asia/Manila')->toDateString();
            $patient->status = PatientStatus::Active;
            $patient->save();

            $this->auditLog->record($user, 'public_registered', 'patients', $patient, 'Public patient registration completed.', null, $patient->withoutRelations()->toArray(), request());

            return $patient;
        });
    }

    public function update(Patient $patient, array $data, User $actor): Patient
    {
        return $this->updatePatient($patient, $data, $actor, false);
    }

    public function updateOwnProfile(Patient $patient, array $data): Patient
    {
        return $this->updatePatient($patient, $data, $patient->user, true);
    }

    public function archive(Patient $patient, User $actor): void
    {
        DB::transaction(function () use ($patient, $actor): void {
            $patient->update(['status' => PatientStatus::Archived, 'updated_by' => $actor->id]);
            $patient->delete();
            $this->auditLog->record($actor, 'archived', 'patients', $patient, 'Patient archived.', null, null, request());
        });
    }

    public function restore(Patient $patient, User $actor): void
    {
        DB::transaction(function () use ($patient, $actor): void {
            $patient->restore();
            $patient->update(['status' => PatientStatus::Active, 'updated_by' => $actor->id]);
            $this->auditLog->record($actor, 'restored', 'patients', $patient, 'Patient restored.', null, null, request());
        });
    }

    public function findPossibleDuplicates(array $data): Collection
    {
        return Patient::query()
            ->where(function ($query) use ($data): void {
                $query->where(function ($query) use ($data): void {
                    $query->where('first_name', $data['first_name'] ?? null)
                        ->where('last_name', $data['last_name'] ?? null)
                        ->whereDate('date_of_birth', $data['date_of_birth'] ?? now()->toDateString());
                })
                    ->when($data['email'] ?? null, fn ($query, $email) => $query->orWhere('email', $email))
                    ->when($data['contact_number'] ?? null, fn ($query, $contact) => $query->orWhere('contact_number', $contact));
            })
            ->limit(5)
            ->get();
    }

    private function createPatient(array $data, User $actor, bool $withAccount): Patient
    {
        $photoPath = $this->storePhoto($data['profile_photo'] ?? null);

        try {
            return DB::transaction(function () use ($data, $actor, $withAccount, $photoPath): Patient {
                $user = null;
                if ($withAccount) {
                    $user = User::create([
                        'name' => trim($data['first_name'].' '.$data['last_name']),
                        'email' => $data['email'],
                        'password' => Hash::make($data['temporary_password']),
                        'email_verified_at' => now(),
                        'status' => UserStatus::Active,
                    ]);
                    $user->assignRole('patient');
                }

                $patient = new Patient($this->patientData($data));
                $patient->patient_number = $this->references->patientNumber();
                $patient->qr_token = $this->qr->generateToken();
                $patient->user_id = $user?->id;
                $patient->profile_photo_path = $photoPath;
                $patient->registration_date = $data['registration_date'] ?? now('Asia/Manila')->toDateString();
                $patient->created_by = $actor->id;
                $patient->updated_by = $actor->id;
                $patient->save();

                $this->auditLog->record($actor, $withAccount ? 'created_with_account' : 'created_without_account', 'patients', $patient, 'Staff-assisted patient registration completed.', null, ['patient_id' => $patient->id, 'with_account' => $withAccount], request());

                return $patient;
            });
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }
    }

    private function updatePatient(Patient $patient, array $data, User $actor, bool $own): Patient
    {
        $newPhoto = $this->storePhoto($data['profile_photo'] ?? null);
        $oldPhoto = $patient->profile_photo_path;

        try {
            return DB::transaction(function () use ($patient, $data, $actor, $own, $newPhoto, $oldPhoto): Patient {
                $old = $patient->withoutRelations()->toArray();
                $patient->fill($this->patientData($data, $own));
                if ($newPhoto) {
                    $patient->profile_photo_path = $newPhoto;
                }
                $patient->updated_by = $actor->id;
                $patient->save();

                if ($patient->user) {
                    $patient->user->forceFill([
                        'name' => $patient->full_name,
                        'email' => $patient->email ?: $patient->user->email,
                    ])->save();
                }

                if ($newPhoto && $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }

                $this->auditLog->record($actor, $own ? 'self_updated' : 'updated', 'patients', $patient, 'Patient profile updated.', $old, $patient->fresh()->withoutRelations()->toArray(), request());

                return $patient;
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $exception;
        }
    }

    private function storePhoto(mixed $file): ?string
    {
        return $file instanceof UploadedFile ? $file->store('patient-photos', 'public') : null;
    }

    private function patientData(array $data, bool $own = false): array
    {
        $fields = ['first_name', 'middle_name', 'last_name', 'suffix', 'date_of_birth', 'sex', 'civil_status', 'email', 'contact_number', 'address_line_1', 'address_line_2', 'barangay', 'city_municipality', 'province', 'postal_code', 'insurance_provider', 'insurance_number'];

        if (! $own) {
            $fields = array_merge($fields, ['blood_type', 'status']);
        }

        return collect($data)->only($fields)->all();
    }
}
