<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Vehicle::class);

        return view('admin.vehicles.create', [
            'statuses' => [
                Vehicle::STATUS_ACTIVE     => 'Aktywny',
                Vehicle::STATUS_IN_SERVICE => 'W serwisie',
                Vehicle::STATUS_RETIRED    => 'Wycofany',
                Vehicle::STATUS_SOLD       => 'Sprzedany',
            ],
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        return redirect()
            ->route('manager.vehicles.show', $vehicle)
            ->with('status', 'Pojazd został dodany.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);
        $vehicle->delete();

        return redirect()
            ->route('manager.vehicles.index')
            ->with('status', 'Pojazd został usunięty.');
    }
}
