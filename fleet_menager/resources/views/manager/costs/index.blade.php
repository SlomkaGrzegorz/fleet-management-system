@extends('layouts.app')

@section('title', 'Koszty')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Koszty floty</h1>
    <a href="{{ route('manager.costs.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded">
        Eksport CSV
    </a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="date" name="from" value="{{ request('from') }}" class="border rounded px-3 py-2">
    <input type="date" name="to"   value="{{ request('to') }}" class="border rounded px-3 py-2">
    <select name="category" class="border rounded px-3 py-2">
        <option value="">-- kategoria --</option>
        @foreach (['fuel', 'service', 'repair', 'insurance', 'tax', 'fine', 'parts', 'other'] as $c)
            <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
        @endforeach
    </select>
    <button class="bg-gray-200 px-4 py-2 rounded">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Data</th>
                <th class="px-3 py-2">Pojazd</th>
                <th class="px-3 py-2">Kategoria</th>
                <th class="px-3 py-2">Wprowadził</th>
                <th class="px-3 py-2 text-right">Kwota</th>
                <th class="px-3 py-2">Opis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($costs as $c)
                <tr class="border-t">
                    <td class="px-3 py-2 font-mono">{{ $c->incurred_at->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $c->vehicle?->plate_number }}</td>
                    <td class="px-3 py-2">{{ $c->category }}</td>
                    <td class="px-3 py-2">{{ $c->enteredBy?->name }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format((float) $c->amount, 2, ',', ' ') }} zł</td>
                    <td class="px-3 py-2">{{ $c->description }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 font-semibold">
            <tr>
                <td colspan="4" class="px-3 py-2 text-right">Suma na stronie:</td>
                <td class="px-3 py-2 text-right font-mono">{{ number_format($pageTotal, 2, ',', ' ') }} zł</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">{{ $costs->links() }}</div>
@endsection
