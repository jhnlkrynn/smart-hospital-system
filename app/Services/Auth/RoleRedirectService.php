<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Support\AccessControl;

class RoleRedirectService
{
    public function redirectPathFor(User $user): string
    {
        foreach (AccessControl::ROLE_DASHBOARDS as $role => $routeName) {
            if ($user->hasRole($role)) {
                return route($routeName, absolute: false);
            }
        }

        return route('account.pending', absolute: false);
    }
}
