<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Alert $alert): bool
    {
        return $user->isManager();
    }

    public function dismiss(User $user, Alert $alert): bool
    {
        return $user->isManager();
    }
}
