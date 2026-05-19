@extends('layouts.app')

@section('title', 'Pojazd ' . $vehicle->plate_number)

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">{{ $vehicle->plate_number }} - {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</h1>
    <div class="flex gap-2">
        <a href="{{ route('manager.assignments.create', $vehicle) }}" class="bg-indigo-600 text-white px-3 py-1 rounded">Przypisz kierowcę</a>
        @if ($vehicle->assigned_user_id)
            <form method="POST" action="{{ route('manager.assignments.destroy', $vehicle) }}"
                  onsubmit="return confirm('Zakończyć przypisanie?');">
                @csrf @method('DELETE')
                <button class="bg-gray-700 text-white px-3 py-1 rounded">Zakończ przypisanie</button>
            </form>
        @endif
        @if (auth()->user()->isAdmin())
            <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                  onsubmit="return confirm('Usunąć pojazd?');">
                @csrf @method('DELETE')
                <button class="bg-red-700 text-white px-3 py-1 rounded">Usuń</button>
            </form>
        @endif
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow space-y-1">
        <div><strong>VIN:</strong> {{ $vehicle->vin ?? '—' }}</div>
        <div><strong>Status:</strong> {{ $vehicle->status }}</div>
        <div><strong>Aktualny kierowca:</strong> {{ $vehicle->assignedDriver?->name ?? '—' }}</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Historia przypisań</h2>
        @forelse ($vehicle->assignments as $a)
            <div class="text-sm py-1 border-b last:border-0">
                {{ $a->user->name }} - {{ $a->assigned_from->format('Y-m-d') }}
                {{ $a->assigned_until ? '→ ' . $a->assigned_until->format('Y-m-d') : ' (trwa)' }}
            </div>
        @empty
            <p class="text-gray-500 text-sm">Brak historii.</p>
        @endforelse
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6 mt-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Ostatnie zgłoszenia</h2>
        @foreach ($vehicle->events as $e)
            <a href="{{ route('manager.events.show', $e) }}" class="block text-sm py-1 border-b last:border-0 hover:bg-gray-50">
                {{ $e->event_date->format('Y-m-d') }} - {{ $e->type }} - {{ $e->status }}
            </a>
        @endforeach
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Ostatnie koszty</h2>
        @foreach ($vehicle->costs as $c)
            <div class="text-sm py-1 border-b last:border-0 flex justify-between">
                <span>{{ $c->incurred_at->format('Y-m-d') }} - {{ $c->category }}</span>
                <span class="font-mono">{{ number_format((float) $c->amount, 2, ',', ' ') }} zł</span>
            </div>
        @endforeach
    </div>
</div>
@endsection
