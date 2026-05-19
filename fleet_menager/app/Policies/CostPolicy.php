<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cost;
use App\Models\User;

class CostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isDriver();
    }

    public function view(User $user, Cost $cost): bool
    {
        if ($user->isManager()) {
            return true;
        }

        if ($user->isDriver()) {
            return $cost->entered_by === $user->id
                || $cost->vehicle?->assigned_user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isManager() || $user->isDriver();
    }

    public function update(User $user, Cost $cost): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $user->isDriver() && $cost->entered_by === $user->id;
    }

    public function delete(User $user, Cost $cost): bool
    {
        return false; // tylko admin
    }

    public function export(User $user): bool
    {
        return $user->isManager();
    }
}
