<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patients.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('patients.view') || ($user->can('patients.view-own-record') && $patient->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('patients.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patients.update') || ($user->can('patients.update-own-profile') && $patient->user_id === $user->id);
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patients.archive');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->can('patients.restore');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return false;
    }

    public function viewQr(User $user, Patient $patient): bool
    {
        return $user->can('patients.view-qr') && ($user->can('patients.view') || $patient->user_id === $user->id);
    }

    public function manageDocuments(User $user, Patient $patient): bool
    {
        return $user->can('patients.manage-documents');
    }
}
