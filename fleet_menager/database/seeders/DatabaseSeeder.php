<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Cost;
use App\Models\Event;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------
        // Konta testowe
        // -------------------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin@fleet.test'],
            [
                'name'     => 'Admin Systemu',
                'password' => Hash::make('password'),
                'role'     => UserRole::Admin->value,
            ],
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@fleet.test'],
            [
                'name'     => 'Anna Manager',
                'password' => Hash::make('password'),
                'role'     => UserRole::Manager->value,
            ],
        );

        $driver1 = User::updateOrCreate(
            ['email' => 'kierowca1@fleet.test'],
            [
                'name'     => 'Jan Kowalski',
                'password' => Hash::make('password'),
                'role'     => UserRole::Driver->value,
            ],
        );

        $driver2 = User::updateOrCreate(
            ['email' => 'kierowca2@fleet.test'],
            [
                'name'     => 'Piotr Nowak',
                'password' => Hash::make('password'),
                'role'     => UserRole::Driver->value,
            ],
        );

        // -------------------------------------------------------------
        // Pojazdy
        // -------------------------------------------------------------
        $v1 = Vehicle::updateOrCreate(['plate_number' => 'WA12345'], [
            'make' => 'Ford', 'model' => 'Transit', 'year' => 2021,
            'vin' => 'WF0XXXTTGXKL12345', 'status' => Vehicle::STATUS_ACTIVE,
            'assigned_user_id' => $driver1->id,
        ]);

        $v2 = Vehicle::updateOrCreate(['plate_number' => 'KR98765'], [
            'make' => 'Volkswagen', 'model' => 'Caddy', 'year' => 2023,
            'vin' => 'WVWZZZ1KZ7W654321', 'status' => Vehicle::STATUS_ACTIVE,
            'assigned_user_id' => $driver2->id,
        ]);

        $v3 = Vehicle::updateOrCreate(['plate_number' => 'GD11111'], [
            'make' => 'Toyota', 'model' => 'Hilux', 'year' => 2022,
            'vin' => 'JTEBU14R908111111', 'status' => Vehicle::STATUS_ACTIVE,
            'assigned_user_id' => null,
        ]);

        // -------------------------------------------------------------
        // Aktywne przypisania
        // -------------------------------------------------------------
        foreach ([[$v1, $driver1], [$v2, $driver2]] as [$v, $d]) {
            VehicleAssignment::updateOrCreate(
                ['vehicle_id' => $v->id, 'user_id' => $d->id, 'assigned_until' => null],
                ['assigned_from' => now()->subMonths(2)->toDateString()],
            );
        }

        // -------------------------------------------------------------
        // Zgłoszenia (ubezpieczenie z terminem + przykładowy incydent)
        // -------------------------------------------------------------
        $insurance = Event::updateOrCreate(
            ['vehicle_id' => $v1->id, 'type' => Event::TYPE_INSURANCE],
            [
                'reported_by' => $manager->id,
                'event_date'  => now()->subMonths(11)->toDateString(),
                'expiry_date' => now()->addDays(20)->toDateString(),
                'notes'       => 'Polisa OC/AC - PZU',
                'status'      => Event::STATUS_OPEN,
            ],
        );

        Event::updateOrCreate(
            ['vehicle_id' => $v2->id, 'type' => Event::TYPE_INCIDENT, 'reported_by' => $driver2->id],
            [
                'event_date' => now()->subDays(3)->toDateString(),
                'notes'      => 'Stłuczka parkingowa - zarysowanie zderzaka tylnego.',
                'status'     => Event::STATUS_OPEN,
            ],
        );

        // -------------------------------------------------------------
        // Koszty
        // -------------------------------------------------------------
        Cost::updateOrCreate(
            ['vehicle_id' => $v1->id, 'incurred_at' => now()->subDays(1)->toDateString(), 'category' => Cost::CATEGORY_FUEL],
            [
                'entered_by'  => $driver1->id,
                'amount'      => 312.45,
                'description' => 'Tankowanie Orlen - faktura 2025/0512',
            ],
        );

        Cost::updateOrCreate(
            ['vehicle_id' => $v2->id, 'incurred_at' => now()->subDays(7)->toDateString(), 'category' => Cost::CATEGORY_SERVICE],
            [
                'entered_by'  => $driver2->id,
                'amount'      => 850.00,
                'description' => 'Wymiana opon letnich',
            ],
        );

        Cost::updateOrCreate(
            ['vehicle_id' => $v1->id, 'event_id' => $insurance->id, 'incurred_at' => now()->subMonths(11)->toDateString(), 'category' => Cost::CATEGORY_INSURANCE],
            [
                'entered_by'  => $manager->id,
                'amount'      => 4200.00,
                'description' => 'Składka roczna OC/AC',
            ],
        );
    }
}
