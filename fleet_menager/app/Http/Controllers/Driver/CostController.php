<?php

declare(strict_types=1);

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCostRequest;
use App\Models\Cost;
use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Kierowca dodaje koszty (np. faktury za paliwo) i przegląda historię.
 */
class CostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cost::class);

        $user = $request->user();

        $costs = Cost::query()
            ->where(function ($q) use ($user) {
                $q->where('entered_by', $user->id)
                  ->orWhereIn('vehicle_id', $user->assignedVehicles()->pluck('id'));
            })
            ->with(['vehicle:id,plate_number'])
            ->latest('incurred_at')
            ->paginate(20);

        return view('driver.costs.index', compact('costs'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Cost::class);

        $vehicles = $request->user()->isDriver()
            ? $request->user()->assignedVehicles()->get()
            : Vehicle::query()->whereNot('status', Vehicle::STATUS_RETIRED)->get();

        return view('driver.costs.create', [
            'vehicles'   => $vehicles,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreCostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['entered_by'] = $request->user()->id;

        DB::transaction(function () use ($request, $data): void {
            $cost = Cost::create([
                'vehicle_id'  => $data['vehicle_id'],
                'event_id'    => $data['event_id'] ?? null,
                'entered_by'  => $data['entered_by'],
                'category'    => $data['category'],
                'amount'      => $data['amount'],
                'incurred_at' => $data['incurred_at'],
                'description' => $data['description'] ?? null,
            ]);

            if ($request->hasFile('invoice')) {
                $file = $request->file('invoice');
                $path = $file->store("invoices/{$cost->vehicle_id}", 'public');

                Document::create([
                    'vehicle_id' => $cost->vehicle_id,
                    'event_id'   => $cost->event_id,
                    'filename'   => $file->getClientOriginalName(),
                    'path'       => $path,
                    'mime_type'  => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }
        });

        return redirect()
            ->route('driver.costs.index')
            ->with('status', 'Koszt został zapisany.');
    }

    public function show(Cost $cost): View
    {
        $this->authorize('view', $cost);
        $cost->load(['vehicle', 'event', 'enteredBy', 'documents']);

        return view('driver.costs.show', compact('cost'));
    }

    /**
     * @return array<string, string>
     */
    private function categoryOptions(): array
    {
        return [
            Cost::CATEGORY_FUEL      => 'Paliwo',
            Cost::CATEGORY_SERVICE   => 'Serwis',
            Cost::CATEGORY_REPAIR    => 'Naprawa',
            Cost::CATEGORY_INSURANCE => 'Ubezpieczenie',
            Cost::CATEGORY_TAX       => 'Podatek',
            Cost::CATEGORY_FINE      => 'Mandat',
            Cost::CATEGORY_PARTS     => 'Części',
            Cost::CATEGORY_OTHER     => 'Inne',
        ];
    }
}
