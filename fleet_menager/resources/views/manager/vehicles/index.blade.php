@extends('layouts.app')

@section('title', 'Pojazdy')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Pojazdy</h1>
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.vehicles.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">+ Dodaj pojazd</a>
    @endif
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Szukaj (nr rej., marka, model)" class="border rounded px-3 py-2">
    <select name="status" class="border rounded px-3 py-2">
        <option value="">-- status --</option>
        @foreach (['active' => 'aktywny', 'in_service' => 'w serwisie', 'retired' => 'wycofany', 'sold' => 'sprzedany'] as $v => $l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
        @endforeach
    </select>
    <button class="bg-gray-200 px-4 py-2 rounded">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-3 py-2">Nr rej.</th>
                <th class="px-3 py-2">Pojazd</th>
                <th class="px-3 py-2">Rok</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Kierowca</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $v)
                <tr class="border-t">
                    <td class="px-3 py-2 font-mono">{{ $v->plate_number }}</td>
                    <td class="px-3 py-2">{{ $v->make }} {{ $v->model }}</td>
                    <td class="px-3 py-2">{{ $v->year }}</td>
                    <td class="px-3 py-2">{{ $v->status }}</td>
                    <td class="px-3 py-2">{{ $v->assignedDriver?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('manager.vehicles.show', $v) }}" class="text-blue-600 hover:underline">Szczegóły</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Brak pojazdów</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $vehicles->links() }}</div>
@endsection
