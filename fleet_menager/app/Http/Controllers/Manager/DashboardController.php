<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Event;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Statystyki cache'owane na 60 sekund - dashboard floty się nie zmienia
        // z sekundy na sekundę, a query'sy z COUNT/SUM są stosunkowo drogie.
        $stats = Cache::remember('manager.dashboard.stats', 60, function (): array {
            return [
                'vehiclesTotal'     => Vehicle::count(),
                'vehiclesAvailable' => Vehicle::available()->count(),
                'driversTotal'      => User::query()->where('role', UserRole::Driver->value)->count(),
                'openIncidents'     => Event::query()->incidents()->open()->count(),
                'monthlyCostsTotal' => (float) Cost::query()
                    ->whereBetween('incurred_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            ];
        });

        // Te listy zostawiamy bez cache - chcemy widzieć świeże terminy od razu.
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

        return view('manager.dashboard', array_merge($stats, [
            'expiringEvents' => $expiringEvents,
            'overdueEvents'  => $overdueEvents,
        ]));
    }
}
