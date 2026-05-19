<?php

declare(strict_types=1);

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $vehicles = $user->assignedVehicles()->get();

        $recentEvents = Event::query()
            ->where('reported_by', $user->id)
            ->latest('event_date')
            ->limit(5)
            ->get();

        $monthlyCosts = Cost::query()
            ->where('entered_by', $user->id)
            ->whereBetween('incurred_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        return view('driver.dashboard', [
            'vehicles'     => $vehicles,
            'recentEvents' => $recentEvents,
            'monthlyCosts' => $monthlyCosts,
        ]);
    }
}
