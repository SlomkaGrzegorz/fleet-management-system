<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Event;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $expiringEvents = Event::query()
            ->expiringSoon(30)
            ->with('vehicle:id,plate_number')
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        $overdueEvents = Event::query()
            ->overdue()
            ->with('vehicle:id,plate_number')
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        return view('manager.dashboard', [
            'vehiclesTotal'     => Vehicle::count(),
            'vehiclesAvailable' => Vehicle::available()->count(),
            'driversTotal'      => User::query()->where('role', UserRole::Driver->value)->count(),
            'openIncidents'     => Event::query()->incidents()->open()->count(),
            'monthlyCostsTotal' => (float) Cost::query()
                ->whereBetween('incurred_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'expiringEvents'    => $expiringEvents,
            'overdueEvents'     => $overdueEvents,
        ]);
    }
}
