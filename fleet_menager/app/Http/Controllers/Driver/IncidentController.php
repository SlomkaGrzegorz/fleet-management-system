<?php

declare(strict_types=1);

namespace App\Http\Controllers\Driver;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Mail\IncidentReported;
use App\Models\Event;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        // Logowanie akcji biznesowej
        Log::channel('fleet')->info('Incident reported', [
            'event_id'   => $event->id,
            'type'       => $event->type,
            'vehicle_id' => $event->vehicle_id,
            'driver_id'  => $event->reported_by,
        ]);

        // Wysyłka maila do wszystkich managerów (driver 'log' = trafia do storage/logs)
        $event->loadMissing(['vehicle', 'reporter']);
        $managers = User::query()->where('role', UserRole::Manager->value)->get();
        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new IncidentReported($event));
        }

        return redirect()
            ->route('driver.incidents.show', $event)
            ->with('status', __('Zgłoszenie zostało zapisane.'));
    }

    public function show(Event $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load(['vehicle', 'reporter', 'costs.enteredBy', 'documents']);

        return view('driver.incidents.show', ['event' => $incident]);
    }
}
