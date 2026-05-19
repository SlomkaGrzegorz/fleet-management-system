<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = Vehicle::query()
            ->with('assignedDriver:id,name')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(function ($qq) use ($term) {
                $qq->where('plate_number', 'like', "%{$term}%")
                   ->orWhere('make', 'like', "%{$term}%")
                   ->orWhere('model', 'like', "%{$term}%");
            }))
            ->orderBy('plate_number')
            ->paginate(20)
            ->withQueryString();

        return view('manager.vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'assignedDriver',
            'assignments.user',
            'events' => fn ($q) => $q->latest('event_date')->limit(20),
            'costs'  => fn ($q) => $q->latest('incurred_at')->limit(20),
        ]);

        return view('manager.vehicles.show', compact('vehicle'));
    }
}
