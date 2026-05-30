<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Statusy upływających terminów (ubezpieczenia, przeglądy, itp.).
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

    /**
     * Endpoint obsługuje zarówno klasyczny form-POST (redirect)
     * jak i AJAX (zwraca JSON). Decyzja na podstawie nagłówka.
     */
    public function dismiss(Request $request, Alert $alert): RedirectResponse|JsonResponse
    {
        $this->authorize('dismiss', $alert);

        $alert->update(['dismissed' => true]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'alertId' => $alert->id,
                'message' => __('Alert oznaczony jako obsłużony.'),
            ]);
        }

        return back()->with('status', __('Alert oznaczony jako obsłużony.'));
    }
}
