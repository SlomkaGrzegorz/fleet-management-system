@extends('layouts.app')

@section('title', 'Przypisz pojazd ' . $vehicle->plate_number)

@section('content')
<h1 class="text-2xl font-semibold mb-4">Przypisz pojazd {{ $vehicle->plate_number }}</h1>

<form method="POST" action="{{ route('manager.assignments.store', $vehicle) }}"
      class="bg-white p-6 rounded shadow space-y-4 max-w-xl">
    @csrf
    <div>
        <label class="block text-sm mb-1">Kierowca</label>
        <select name="user_id" required class="w-full border rounded px-3 py-2">
            @foreach ($drivers as $d)
                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->email }})</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm mb-1">Od</label>
            <input type="date" name="assigned_from" value="{{ now()->toDateString() }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Do (opcjonalnie)</label>
            <input type="date" name="assigned_until" class="w-full border rounded px-3 py-2">
        </div>
    </div>
    <button class="bg-indigo-600 text-white px-4 py-2 rounded">Przypisz</button>
</form>
@endsection
