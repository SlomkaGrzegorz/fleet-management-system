<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Vehicle::class);

        return view('admin.vehicles.create', [
            'statuses' => [
                Vehicle::STATUS_ACTIVE     => __('Aktywny'),
                Vehicle::STATUS_IN_SERVICE => __('W serwisie'),
                Vehicle::STATUS_RETIRED    => __('Wycofany'),
                Vehicle::STATUS_SOLD       => __('Sprzedany'),
            ],
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        Log::channel('fleet')->info('Vehicle added by admin', [
            'vehicle_id'   => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'admin_id'     => $request->user()->id,
        ]);

        return redirect()
            ->route('manager.vehicles.show', $vehicle)
            ->with('status', __('Pojazd został dodany.'));
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);

        $plate = $vehicle->plate_number;
        $id = $vehicle->id;
        $vehicle->delete();

        Log::channel('fleet')->warning('Vehicle deleted by admin', [
            'vehicle_id'   => $id,
            'plate_number' => $plate,
            'admin_id'     => auth()->id(),
        ]);

        return redirect()
            ->route('manager.vehicles.index')
            ->with('status', __('Pojazd został usunięty.'));
    }
}
