<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Role użytkowników w systemie Fleet Manager.
 *
 * Wartości muszą zgadzać się z ENUM-em w migracji
 * 2026_05_19_124117_add_role_to_users_table.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Driver = 'driver';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Fleet Manager',
            self::Driver => 'Kierowca',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
