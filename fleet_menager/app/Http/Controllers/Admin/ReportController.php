<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Event;
use App\Models\Vehicle;
use App\Services\CostCsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Raporty dla admina: zbiorcze podsumowania kosztów, pełen eksport CSV.
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfYear();
        $to   = $request->date('to') ?? now()->endOfDay();

        $byCategory = Cost::query()
            ->whereBetween('incurred_at', [$from, $to])
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $byVehicle = Cost::query()
            ->whereBetween('incurred_at', [$from, $to])
            ->select('vehicle_id', DB::raw('SUM(amount) as total'))
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->with('vehicle:id,plate_number,make,model')
            ->limit(20)
            ->get();

        $totalSum = (float) Cost::query()
            ->whereBetween('incurred_at', [$from, $to])
            ->sum('amount');

        return view('admin.reports.index', [
            'from'        => $from,
            'to'          => $to,
            'byCategory'  => $byCategory,
            'byVehicle'   => $byVehicle,
            'totalSum'    => $totalSum,
            'incidents'   => Event::query()->incidents()->whereBetween('event_date', [$from, $to])->count(),
            'vehiclesCnt' => Vehicle::count(),
        ]);
    }

    public function exportCosts(Request $request, CostCsvExporter $exporter): StreamedResponse
    {
        return $exporter->streamResponse($request->only(['from', 'to', 'category', 'vehicle_id']));
    }
}
