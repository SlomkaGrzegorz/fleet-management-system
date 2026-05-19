<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

/**
 * Admin może usuwać zgłoszenia w razie błędów - to jego wyłączna prerogatywa.
 */
class EventController extends Controller
{
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);
        $event->delete();

        return redirect()
            ->route('manager.events.index')
            ->with('status', 'Zgłoszenie zostało usunięte.');
    }
}
