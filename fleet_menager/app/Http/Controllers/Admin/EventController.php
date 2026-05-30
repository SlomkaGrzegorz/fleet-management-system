<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Admin może usuwać zgłoszenia w razie błędów - to jego wyłączna prerogatywa.
 */
class EventController extends Controller
{
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $id = $event->id;
        $type = $event->type;
        $vehicleId = $event->vehicle_id;
        $event->delete();

        Log::channel('fleet')->warning('Event deleted by admin', [
            'event_id'   => $id,
            'type'       => $type,
            'vehicle_id' => $vehicleId,
            'admin_id'   => auth()->id(),
        ]);

        return redirect()
            ->route('manager.events.index')
            ->with('status', __('Zgłoszenie zostało usunięte.'));
    }
}
