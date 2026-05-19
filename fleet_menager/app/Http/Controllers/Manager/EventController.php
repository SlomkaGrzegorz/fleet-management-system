<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::query()
            ->with(['vehicle:id,plate_number', 'reporter:id,name'])
            ->when($request->string('type')->toString(), fn ($q, $t) => $q->where('type', $t))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('event_date')
            ->paginate(25)
            ->withQueryString();

        return view('manager.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);
        $event->load(['vehicle', 'reporter', 'costs.enteredBy', 'documents']);

        return view('manager.events.show', compact('event'));
    }

    public function updateStatus(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Event::STATUS_OPEN,
                Event::STATUS_IN_PROGRESS,
                Event::STATUS_CLOSED,
            ]),
        ]);

        $event->update(['status' => $data['status']]);

        return back()->with('status', 'Status zgłoszenia zaktualizowany.');
    }
}
