@extends('layouts.app')

@section('title', 'Moje koszty')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Moje koszty</h1>
    <a href="{{ route('driver.costs.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Nowy koszt</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Data</th>
                <th class="px-3 py-2">Pojazd</th>
                <th class="px-3 py-2">Kategoria</th>
                <th class="px-3 py-2 text-right">Kwota</th>
                <th class="px-3 py-2">Opis</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($costs as $c)
                <tr class="border-t">
                    <td class="px-3 py-2 font-mono">{{ $c->incurred_at->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $c->vehicle?->plate_number }}</td>
                    <td class="px-3 py-2">{{ $c->category }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format((float) $c->amount, 2, ',', ' ') }} zł</td>
                    <td class="px-3 py-2">{{ $c->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Brak kosztów</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $costs->links() }}</div>
@endsection
