@extends('layouts.app')

@section('title', 'Raporty')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Raporty</h1>
    <a href="{{ route('admin.reports.costs.export', request()->query()) }}"
       class="bg-green-600 text-white px-4 py-2 rounded">Pobierz pełny CSV</a>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="border rounded px-3 py-2">
    <input type="date" name="to"   value="{{ request('to', $to->format('Y-m-d')) }}" class="border rounded px-3 py-2">
    <button class="bg-gray-200 px-4 py-2 rounded">Pokaż</button>
</form>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Pojazdy w systemie</div>
        <div class="text-2xl font-semibold">{{ $vehiclesCnt }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Incydenty (okres)</div>
        <div class="text-2xl font-semibold">{{ $incidents }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Suma kosztów (okres)</div>
        <div class="text-2xl font-semibold">{{ number_format($totalSum, 2, ',', ' ') }} zł</div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Koszty wg kategorii</h2>
        <table class="w-full text-sm">
            <thead class="text-left"><tr><th>Kategoria</th><th class="text-right">Liczba</th><th class="text-right">Suma</th></tr></thead>
            <tbody>
                @foreach ($byCategory as $row)
                    <tr class="border-t">
                        <td class="py-1">{{ $row->category }}</td>
                        <td class="py-1 text-right">{{ $row->cnt }}</td>
                        <td class="py-1 text-right font-mono">{{ number_format((float) $row->total, 2, ',', ' ') }} zł</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Top pojazdy wg kosztów</h2>
        <table class="w-full text-sm">
            <thead class="text-left"><tr><th>Pojazd</th><th class="text-right">Suma</th></tr></thead>
            <tbody>
                @foreach ($byVehicle as $row)
                    <tr class="border-t">
                        <td class="py-1">{{ $row->vehicle?->plate_number }} - {{ $row->vehicle?->make }} {{ $row->vehicle?->model }}</td>
                        <td class="py-1 text-right font-mono">{{ number_format((float) $row->total, 2, ',', ' ') }} zł</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
