<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isDriver();
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isManager()) {
            return true;
        }

        // Kierowca widzi swoje zgłoszenia oraz zgłoszenia dot. jego pojazdu.
        if ($user->isDriver()) {
            return $event->reported_by === $user->id
                || $event->vehicle?->assigned_user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Kierowca może zgłaszać incydenty, manager też (np. wpis serwisowy),
        // admin przechodzi przez before().
        return $user->isManager() || $user->isDriver();
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->isManager()) {
            return true;
        }

        // Kierowca może edytować tylko swoje zgłoszenie i tylko gdy jest open.
        return $user->isDriver()
            && $event->reported_by === $user->id
            && $event->status === Event::STATUS_OPEN;
    }

    public function delete(User $user, Event $event): bool
    {
        // Usuwanie zgłoszeń - tylko admin (wymaganie z opisu).
        return false;
    }
}
