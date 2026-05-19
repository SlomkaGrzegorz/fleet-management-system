<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cost;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generator pliku CSV z kosztami floty - do wysłania do księgowości.
 */
class CostCsvExporter
{
    /**
     * @param  array{from?: string, to?: string, vehicle_id?: int, category?: string}  $filters
     */
    public function streamResponse(array $filters = []): StreamedResponse
    {
        $filename = sprintf('koszty_%s.csv', now()->format('Y-m-d_His'));

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($filters): void {
            $handle = fopen('php://output', 'w');

            // BOM dla Excela, żeby polskie znaki nie zepsuły się przy otwarciu.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID', 'Data', 'Pojazd', 'Nr rejestracyjny', 'Kategoria',
                'Kwota (PLN)', 'Opis', 'Wprowadził', 'Zgłoszenie ID',
            ], ';');

            $this->buildQuery($filters)
                ->with(['vehicle:id,plate_number,make,model', 'enteredBy:id,name'])
                ->orderBy('incurred_at')
                ->chunk(500, function ($costs) use ($handle): void {
                    foreach ($costs as $cost) {
                        /** @var Cost $cost */
                        fputcsv($handle, [
                            $cost->id,
                            $cost->incurred_at instanceof CarbonInterface
                                ? $cost->incurred_at->format('Y-m-d')
                                : (string) $cost->incurred_at,
                            trim(($cost->vehicle?->make ?? '') . ' ' . ($cost->vehicle?->model ?? '')),
                            $cost->vehicle?->plate_number ?? '',
                            $cost->category,
                            number_format((float) $cost->amount, 2, ',', ''),
                            $cost->description ?? '',
                            $cost->enteredBy?->name ?? '',
                            $cost->event_id ?? '',
                        ], ';');
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * @param  array{from?: string, to?: string, vehicle_id?: int, category?: string}  $filters
     */
    private function buildQuery(array $filters): Builder
    {
        $query = Cost::query();

        if (! empty($filters['from'])) {
            $query->whereDate('incurred_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('incurred_at', '<=', $filters['to']);
        }

        if (! empty($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query;
    }
}
