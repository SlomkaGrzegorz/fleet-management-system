<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Admin ma dostęp do wszystkiego.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isDriver();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->isManager()) {
            return true;
        }

        // Kierowca widzi tylko swoje pojazdy.
        return $user->isDriver() && $vehicle->assigned_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Dodawanie pojazdów wg wymagań: tylko admin.
        // before() już zwróciło true dla admina, więc tutaj false.
        return false;
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return false; // tylko admin (przechodzi przez before())
    }

    public function assign(User $user, Vehicle $vehicle): bool
    {
        return $user->isManager();
    }
}
