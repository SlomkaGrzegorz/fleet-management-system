<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignVehicleRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function create(Vehicle $vehicle): View
    {
        $this->authorize('assign', $vehicle);

        $drivers = User::query()
            ->where('role', UserRole::Driver->value)
            ->orderBy('name')
            ->get();

        return view('manager.assignments.create', compact('vehicle', 'drivers'));
    }

    public function store(AssignVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('assign', $vehicle);

        $data = $request->validated();

        DB::transaction(function () use ($vehicle, $data): void {
            // Zamknij poprzednie aktywne przypisanie, jeśli istnieje.
            VehicleAssignment::query()
                ->where('vehicle_id', $vehicle->id)
                ->whereNull('assigned_until')
                ->update(['assigned_until' => now()->toDateString()]);

            VehicleAssignment::create([
                'vehicle_id'    => $vehicle->id,
                'user_id'       => $data['user_id'],
                'assigned_from' => $data['assigned_from'],
                'assigned_until'=> $data['assigned_until'] ?? null,
            ]);

            $vehicle->update(['assigned_user_id' => $data['user_id']]);
        });

        return redirect()
            ->route('manager.vehicles.show', $vehicle)
            ->with('status', 'Pojazd został przypisany kierowcy.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('assign', $vehicle);

        DB::transaction(function () use ($vehicle): void {
            VehicleAssignment::query()
                ->where('vehicle_id', $vehicle->id)
                ->whereNull('assigned_until')
                ->update(['assigned_until' => now()->toDateString()]);

            $vehicle->update(['assigned_user_id' => null]);
        });

        return redirect()
            ->route('manager.vehicles.show', $vehicle)
            ->with('status', 'Przypisanie zostało zakończone.');
    }
}
