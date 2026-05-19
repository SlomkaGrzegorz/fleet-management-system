<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Statusy upływających terminów (ubezpieczenia, przeglądy, itp.).
 * Lista bazuje na eventach z expiry_date - zarówno tych, które
 * są blisko terminu, jak i tych przeterminowanych.
 */
class AlertController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Alert::class);

        $expiring = Event::query()
            ->expiringSoon(30)
            ->with('vehicle:id,plate_number,make,model')
            ->orderBy('expiry_date')
            ->get();

        $overdue = Event::query()
            ->overdue()
            ->with('vehicle:id,plate_number,make,model')
            ->orderBy('expiry_date')
            ->get();

        $stored = Alert::query()
            ->active()
            ->due()
            ->with(['vehicle:id,plate_number', 'event:id,type,expiry_date'])
            ->orderBy('trigger_date')
            ->get();

        return view('manager.alerts.index', compact('expiring', 'overdue', 'stored'));
    }

    public function dismiss(Alert $alert): RedirectResponse
    {
        $this->authorize('dismiss', $alert);

        $alert->update(['dismissed' => true]);

        return back()->with('status', 'Alert oznaczony jako obsłużony.');
    }
}
