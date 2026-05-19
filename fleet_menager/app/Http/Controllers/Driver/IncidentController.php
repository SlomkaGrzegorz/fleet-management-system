<?php

declare(strict_types=1);

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Models\Event;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Kontroler dla kierowcy do zgłaszania incydentów / serwisu / napraw.
 * Admin też tu wchodzi (middleware role:driver przepuszcza go w before()).
 */
class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $user = $request->user();

        $events = Event::query()
            ->where(function ($q) use ($user) {
                $q->where('reported_by', $user->id)
                  ->orWhereIn('vehicle_id', $user->assignedVehicles()->pluck('id'));
            })
            ->with(['vehicle:id,plate_number,make,model'])
            ->latest('event_date')
            ->paginate(20);

        return view('driver.incidents.index', compact('events'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Event::class);

        // Kierowca może zgłaszać tylko dla swoich pojazdów.
        // Admin/manager wpadający tu - dla wszystkich aktywnych.
        $vehicles = $request->user()->isDriver()
            ? $request->user()->assignedVehicles()->get()
            : Vehicle::query()->whereNot('status', Vehicle::STATUS_RETIRED)->get();

        return view('driver.incidents.create', compact('vehicles'));
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['reported_by'] = $request->user()->id;
        $data['status'] = Event::STATUS_OPEN;

        $event = Event::create($data);

        return redirect()
            ->route('driver.incidents.show', $event)
            ->with('status', 'Zgłoszenie zostało zapisane.');
    }

    public function show(Event $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load(['vehicle', 'reporter', 'costs.enteredBy', 'documents']);

        return view('driver.incidents.show', ['event' => $incident]);
    }
}
