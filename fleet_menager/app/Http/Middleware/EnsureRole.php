<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware do prostej autoryzacji opartej na enumie roli użytkownika.
 *
 * Użycie w trasie: ->middleware('role:admin,manager')
 *
 * Admin zawsze przechodzi (nawet jeśli nie jest wprost wymieniony),
 * dzięki czemu administrator może wejść we wszystkie ścieżki ról
 * niższych (zgodnie z wymaganiem: admin ma móc zrobić to co kierowca
 * i fleet manager).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $allowed = array_filter(array_map(
            static fn (string $role) => UserRole::tryFrom($role),
            $roles,
        ));

        if (! $user->hasRole(...$allowed)) {
            abort(403, 'Brak uprawnień do tej sekcji.');
        }

        return $next($request);
    }
}
