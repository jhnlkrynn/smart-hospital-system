<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employees.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('employees.view') || $employee->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($this->isProtectedSuperAdmin($user, $employee)) {
            return false;
        }

        return $user->can('employees.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        if ($this->isProtectedSuperAdmin($user, $employee)) {
            return false;
        }

        if ($employee->user?->hasRole('super-admin') && User::role('super-admin')->where('status', 'active')->count() <= 1) {
            return false;
        }

        return $user->can('employees.archive');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->can('employees.restore');
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }

    private function isProtectedSuperAdmin(User $actor, Employee $employee): bool
    {
        return $employee->user?->hasRole('super-admin') && ! $actor->hasRole('super-admin');
    }
}
