<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->isManager();
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->isManager()) {
            return $target->isDriver();
        }

        return $actor->id === $target->id;
    }

    public function manage(User $actor): bool
    {
        return false; // tylko admin
    }
}
