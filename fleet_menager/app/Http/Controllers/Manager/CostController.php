<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Services\CostCsvExporter;
use App\Mail\CostReportMail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;


class CostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cost::class);

        $costs = Cost::query()
            ->with(['vehicle:id,plate_number', 'enteredBy:id,name'])
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('incurred_at', '>=', $from))
            ->when($request->date('to'),   fn ($q, $to)   => $q->whereDate('incurred_at', '<=', $to))
            ->when($request->string('category')->toString(), fn ($q, $c) => $q->where('category', $c))
            ->when($request->integer('vehicle_id'), fn ($q, $v) => $q->where('vehicle_id', $v))
            ->latest('incurred_at')
            ->paginate(25)
            ->withQueryString();

        $total = (float) (clone $costs->getCollection())->sum('amount');

        return view('manager.costs.index', [
            'costs'         => $costs,
            'pageTotal'     => $total,
        ]);
    }

    public function export(Request $request, CostCsvExporter $exporter): RedirectResponse
    {

        $this->authorize('export', Cost::class);

        $filters = $request->only(['from', 'to', 'category', 'vehicle_id']);


        $response = $exporter->streamResponse($filters);
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();


        $userEmail = auth()->user()->email;

        $fileName = 'raport_kosztow_' . now()->format('Y-m-d') . '.csv';

        Mail::to($userEmail)->send(new CostReportMail($csvContent, $fileName));

        return back()->with('success', 'Raport został pomyślnie wygenerowany i wysłany na Twój adres e-mail (' . $userEmail . ')!');
    }
}
